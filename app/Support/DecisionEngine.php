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
     * Each entry: [code, challenge, action, severity, min_count]
     */
    private const RULES = [
        ['invoice.overdue', 'Ausstehende Rechnungen', 'Outstanding invoices — contact the debtor and set a payment reminder.', 'warning', 1],
        ['order.overdue', 'Verspätete Aufträge', 'Production orders are late — re-plan the affected orders and inform the customer.', 'critical', 1],
        ['machine.maintenance', 'Maschinenstillstand', 'A machine is in maintenance — reschedule its open orders to free capacity.', 'warning', 1],
        ['complaint', 'Kundenreklamation', 'A complaint was filed — review the case and record a corrective measure.', 'critical', 1],
        ['report.missing', 'Fehlende Berichte', 'A monthly report is missing — file the missing financial report for the company.', 'info', 1],
        ['deadline.due', 'Nahende Frist', 'A compliance deadline is near — complete the task before it expires.', 'warning', 1],
        ['risk.high', 'Hohes Risiko', 'A high-risk assessment is open — assign a responsible person and define measures.', 'critical', 1],
    ];

    /** Run the engine: scan recent signals, upsert open recommendations per company. */
    public function run(): int
    {
        $created = 0;

        foreach (self::RULES as [$prefix, $challenge, $action, $severity, $min]) {
            $signals = Signal::query()
                ->where('type', 'like', $prefix.'%')
                ->where('occurred_at', '>=', now()->subDays(30))
                ->get();

            $pattern = Pattern::firstOrCreate(
                ['code' => $prefix],
                ['challenge' => $challenge, 'companies_count' => 0]
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
                    'title' => $challenge,
                    'description' => $action,
                    'evidence' => $this->evidence($prefix, $companySignals->count()),
                    'severity' => $severity,
                    'status' => 'open',
                ]);
                $created++;
            }
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
