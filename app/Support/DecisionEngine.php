<?php

namespace App\Support;

use App\Models\Outcome;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;

/**
 * Decision engine v1 — rule-based over signals + recorded outcomes.
 *
 * Loop: signals → detected challenges (patterns) → recommendations
 *       → user acts → outcome recorded → effectiveness stats feed the
 *       "x% of similar companies succeeded" evidence text.
 */
class DecisionEngine
{
    /**
     * Rules: signal type prefix → challenge + recommended action.
     * Each entry: [code, challenge_de, challenge_en, action_de, action_en, severity, min_count, effort, responsible]
     */
    private const RULES = [
        ['invoice.overdue', 'Ausstehende Rechnungen', 'Outstanding invoices', 'Offene Rechnungen — Schuldner kontaktieren und Zahlungserinnerung setzen.', 'Outstanding invoices — contact the debtor and set a payment reminder.', 'warning', 1, 'small', 'Buchhaltung'],
        ['order.overdue', 'Verspätete Aufträge', 'Late orders', 'Produktionsaufträge sind verspätet — betroffene Aufträge neu planen und Kunden informieren.', 'Production orders are late — re-plan the affected orders and inform the customer.', 'critical', 1, 'medium', 'Produktionsleitung'],
        ['machine.maintenance', 'Maschinenstillstand', 'Machine downtime', 'Eine Maschine ist in Wartung — offene Aufträge auf freie Kapazität verlegen.', 'A machine is in maintenance — reschedule its open orders to free capacity.', 'warning', 1, 'small', 'Instandhaltung'],
        ['complaint', 'Kundenreklamation', 'Customer complaint', 'Eine Reklamation wurde eingereicht — Fall prüfen und Korrekturmaßnahme erfassen.', 'A complaint was filed — review the case and record a corrective measure.', 'critical', 1, 'medium', 'Qualitätsmanagement'],
        ['report.missing', 'Fehlende Berichte', 'Missing reports', 'Ein Monatsbericht fehlt — fehlenden Finanzbericht für das Unternehmen einreichen.', 'A monthly report is missing — file the missing financial report for the company.', 'info', 1, 'small', 'Controlling'],
        ['deadline.due', 'Nahende Frist', 'Upcoming deadline', 'Eine Compliance-Frist läuft bald ab — Aufgabe vor Ablauf erledigen.', 'A compliance deadline is near — complete the task before it expires.', 'warning', 1, 'small', 'Fachverantwortlicher'],
        ['risk.high', 'Hohes Risiko', 'High risk', 'Eine Risikobeurteilung mit hohem Risiko ist offen — Verantwortlichen zuweisen und Maßnahmen definieren.', 'A high-risk assessment is open — assign a responsible person and define measures.', 'critical', 1, 'large', 'Geschäftsführung'],
        ['order_complaint', 'Reklamation im Auftrag', 'Order complaint', 'Eine Reklamation wurde zu einem Zahntechnik-Auftrag gemeldet — Fall prüfen und Korrekturmaßnahme einleiten.', 'A complaint was filed on a dental-lab order — review the case and start a corrective measure.', 'critical', 1, 'medium', 'Qualitätsmanagement'],
        ['order_lead_time', 'Lange Durchlaufzeit', 'Long lead time', 'Ein Auftrag hat eine lange Durchlaufzeit — Prozessschritte prüfen und Engpässe beseitigen.', 'An order shows a long lead time — review the process steps and remove bottlenecks.', 'warning', 1, 'medium', 'Produktionsleitung'],
        ['lead_qualified', 'Qualifizierter Lead wartet', 'Qualified lead waiting', 'Ein Lead wurde qualifiziert — zeitnah kontaktieren und Angebot erstellen.', 'A lead was qualified — reach out promptly and prepare an offer.', 'info', 1, 'small', 'Vertrieb'],
        ['timeentry_billable', 'Unberechnete Stunden', 'Unbilled hours', 'Abrechenbare Zeiteinträge liegen vor — Stunden in eine Rechnung überführen.', 'Billable time entries exist — convert the hours into an invoice.', 'info', 5, 'small', 'Buchhaltung'],
    ];

    /** Run the engine: scan recent signals, upsert open recommendations per company. */
    public function run(): int
    {
        Recommendation::query()
            ->where('status', 'open')
            ->where('created_at', '<', now()->subDays(45))
            ->update(['status' => 'expired']);

        $created = $this->runSuggestions();

        $this->runResolutions();

        foreach (self::RULES as [$prefix, $challengeDe, $challengeEn, $actionDe, $actionEn, $severity, $min, $effort, $responsible]) {
            $signals = Signal::query()
                ->where('type', 'like', $prefix.'%')
                ->where('occurred_at', '>=', now()->subDays(30))
                ->get();

            $pattern = Pattern::firstOrCreate(
                ['code' => $prefix],
                ['challenge' => $challengeDe, 'companies_count' => 0]
            );

            $byCompany = $signals->groupBy(fn (Signal $s) => $s->company_key ?: 'default');
            $pattern->companies_count = $byCompany->count();
            $pattern->evidence = ['signals_30d' => $signals->count()];
            $pattern->save();

            foreach ($byCompany as $companyKey => $companySignals) {
                if ($companySignals->count() < $min) {
                    continue;
                }

                $exists = Recommendation::query()
                    ->where('company_key', $companyKey)
                    ->where('code', $prefix)
                    ->whereIn('status', ['open', 'accepted'])
                    ->exists();
                if ($exists) {
                    continue;
                }

                Recommendation::create([
                    'company_key' => $companyKey,
                    'pattern_id' => $pattern->id,
                    'code' => $prefix,
                    'title' => $challengeDe,
                    'title_en' => $challengeEn,
                    'description' => $actionDe,
                    'description_en' => $actionEn,
                    'evidence' => $this->evidence($prefix, $companySignals->count()) + [
                        'effort' => $effort,
                        'responsible' => $responsible,
                    ],
                    'severity' => $severity,
                    'status' => 'open',
                ]);
                $created++;
            }
        }

        return $created;
    }

    /**
     * Auto-resolution: a positive signal closes matching open cards.
     * signal type → recommendation code it resolves.
     */
    private const RESOLVERS = [
        'invoice_paid' => 'invoice.overdue',
        'payment_received' => 'invoice.overdue',
        'order_done' => 'order_complaint',
    ];

    private function runResolutions(): void
    {
        foreach (self::RESOLVERS as $signalType => $code) {
            $companies = Signal::query()
                ->where('type', $signalType)
                ->where('occurred_at', '>=', now()->subDays(30))
                ->pluck('company_key')
                ->filter()
                ->unique();

            if ($companies->isEmpty()) {
                continue;
            }

            Recommendation::query()
                ->where('code', $code)
                ->where('status', 'open')
                ->whereIn('company_key', $companies)
                ->get()
                ->each(function (Recommendation $rec): void {
                    $rec->update(['status' => 'done']);
                    Outcome::create([
                        'recommendation_id' => $rec->id,
                        'result' => 'success',
                        'note' => 'auto-resolved by signal',
                    ]);
                });
        }
    }

    /**
     * Payload-driven suggestions (e.g. audit findings from allocore.de):
     * signal type 'audit.suggestion' with payload {ref_id, issue, solution,
     * responsible, effort, status, finding_title} → advice card per company.
     */
    private function runSuggestions(): int
    {
        $created = 0;

        $pattern = Pattern::firstOrCreate(
            ['code' => 'audit.suggestion'],
            ['challenge' => 'Audit-Empfehlung', 'companies_count' => 0]
        );

        $signals = Signal::query()
            ->where('type', 'audit.suggestion')
            ->where('occurred_at', '>=', now()->subDays(90))
            ->get();

        $pattern->companies_count = $signals->whereNotNull('company_key')->pluck('company_key')->unique()->count();
        $pattern->evidence = ['suggestions' => $signals->count()];
        $pattern->save();

        foreach ($signals as $signal) {
            $payload = $signal->payload ?? [];
            $ref = (string) ($payload['ref_id'] ?? $signal->id);

            $exists = Recommendation::query()
                ->where('code', 'audit.suggestion')
                ->where('evidence->ref_id', $ref)
                ->exists();
            if ($exists) {
                continue;
            }

            $issue = (string) ($payload['issue'] ?? 'Audit recommendation');
            $solution = (string) ($payload['solution'] ?? '');
            $finding = (string) ($payload['finding_title'] ?? '');
            $effort = (string) ($payload['effort'] ?? '');

            Recommendation::create([
                'company_key' => $signal->company_key,
                'pattern_id' => $pattern->id,
                'code' => 'audit.suggestion',
                'title' => $issue,
                'title_en' => $issue,
                'description' => trim(($solution ? $solution.'. ' : '').($finding ? "Audit: {$finding}" : '')),
                'description_en' => trim(($solution ? $solution.'. ' : '').($finding ? "Audit: {$finding}" : '')),
                'evidence' => $this->evidence('audit.suggestion', 1) + [
                    'ref_id' => $ref,
                    'effort' => $effort,
                    'responsible' => $payload['responsible'] ?? null,
                ],
                'severity' => $effort === 'large' ? 'warning' : 'info',
                'status' => 'open',
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * Effectiveness evidence for a recommendation code:
     * past outcomes across all companies → "x% of companies succeeded".
     */
    private function evidence(string $code, int $signalCount): array
    {
        $stats = self::effectiveness($code);

        return [
            'signals' => $signalCount,
            'companies_tried' => $stats['tried'],
            'success_rate' => $stats['success_rate'],
            'text' => $stats['tried'] > 0
                ? "{$stats['tried']} companies implemented a similar action; {$stats['success_rate']}% succeeded."
                : 'First detected case — effectiveness will be measured from your outcome.',
        ];
    }

    /** Success stats for a code across all recommendations. */
    public static function effectiveness(string $code): array
    {
        $ids = Recommendation::where('code', $code)->pluck('id');
        $outcomes = Outcome::whereIn('recommendation_id', $ids)
            ->whereIn('result', ['success', 'failed'])
            ->get();

        $tried = $outcomes->count();
        $successRate = $tried > 0 ? (int) round(100 * $outcomes->where('result', 'success')->count() / $tried) : 0;

        return ['tried' => $tried, 'success_rate' => $successRate];
    }
}
