<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Support\Facades\DB;

class IntelligenceController extends Controller
{
    /**
     * What a platform manager sees: product intelligence for their platform.
     */
    public function platform(Source $source)
    {
        $challengeCounts = Signal::where('source_id', $source->id)
            ->whereNotNull('challenge_key')
            ->select('challenge_key', DB::raw('count(*) as total'))
            ->groupBy('challenge_key')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $recommendationIds = Recommendation::whereIn(
            'company_id',
            Company::where('source_id', $source->id)->select('id')
        )->select('id');

        $effectiveness = Outcome::whereIn('recommendation_id', $recommendationIds)
            ->select('result', DB::raw('count(*) as total'))
            ->groupBy('result')
            ->pluck('total', 'result');

        $topUsers = Signal::where('source_id', $source->id)
            ->whereNotNull('external_user_id')
            ->select('external_user_id', DB::raw('count(*) as total'))
            ->groupBy('external_user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Feature improvement opportunities: challenges rising on this
        // platform in the last 30 days vs the prior 30.
        $rising = Signal::where('source_id', $source->id)
            ->whereNotNull('challenge_key')
            ->where('occurred_at', '>=', now()->subDays(30))
            ->select('challenge_key', DB::raw('count(*) as total'))
            ->groupBy('challenge_key')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $companies = Company::where('source_id', $source->id)
            ->withCount(['signals as signals_30d' => fn ($q) => $q->where('occurred_at', '>=', now()->subDays(30))])
            ->withCount(['recommendations as pending_recs' => fn ($q) => $q->where('status', 'pending')])
            ->withCount(['recommendations as unmeasured' => fn ($q) => $q->where('status', 'implemented')->whereDoesntHave('outcome')])
            ->withMax('signals', 'occurred_at')
            ->get()
            ->map(function ($c) {
                $c->top_challenge = $c->signals()->whereNotNull('challenge_key')
                    ->select('challenge_key', DB::raw('count(*) as n'))
                    ->groupBy('challenge_key')->orderByDesc('n')->value('challenge_key');

                return $c;
            })
            ->sortByDesc('signals_30d')->values();

        return view('intelligence.platform', [
            'source' => $source,
            'challengeCounts' => $challengeCounts,
            'effectiveness' => $effectiveness,
            'topUsers' => $topUsers,
            'risingChallenges' => $rising,
            'companies' => $companies,
        ]);
    }

    /**
     * What the Allocore Manager team sees: learning-system quality metrics.
     */
    public function allocore()
    {
        return view('intelligence.allocore', [
            'companies' => Company::count(),
            'signals' => Signal::count(),
            'recommendations' => Recommendation::count(),
            'outcomes' => Outcome::count(),
            'outcomeCoverage' => Recommendation::count() > 0
                ? round(Recommendation::whereHas('outcome')->count() / Recommendation::count() * 100, 1)
                : null,
            'adoptionRate' => Recommendation::count() > 0
                ? round(Recommendation::whereIn('status', ['accepted', 'implemented'])->count() / Recommendation::count() * 100, 1)
                : null,
            'overallSuccessRate' => Outcome::count() > 0
                ? round(Outcome::whereIn('result', ['success', 'partial'])->count() / Outcome::count() * 100, 1)
                : null,
            'patterns' => Pattern::with('actionMeasure')->orderByDesc('attempts')->limit(50)->get(),
            // Evidence health: how much of what the system knows is still fresh.
            'evidenceHealth' => [
                'fresh' => Pattern::where('last_outcome_at', '>=', now()->subDays(90))->count(),
                'aging' => Pattern::whereBetween('last_outcome_at', [now()->subDays(180), now()->subDays(90)])->count(),
                'stale' => Pattern::where('last_outcome_at', '<', now()->subDays(180))->count(),
                'never' => Pattern::whereNull('last_outcome_at')->count(),
            ],
            'coverageGaps' => $this->coverageGaps(),
            'calibration' => $this->calibration(),
            'challengeAliases' => $this->challengeAliases(),
            // Learning performance: how fast new evidence is being absorbed.
            'outcomes7d' => Outcome::where('measured_at', '>=', now()->subDays(7))->count(),
            'outcomesPrev7d' => Outcome::whereBetween('measured_at', [now()->subDays(14), now()->subDays(7)])->count(),
            // Why advice gets ignored: dismissal reasons learned from
            // pattern failure_reasons (keys prefixed 'dismissed_').
            'dismissReasons' => Pattern::whereNotNull('failure_reasons')
                ->pluck('failure_reasons')
                ->flatMap(fn ($r) => collect($r ?? [])->filter(fn ($c, $k) => str_starts_with($k, 'dismissed_')))
                ->reduce(fn ($agg, $c, $k) => $agg->put($k, ($agg[$k] ?? 0) + $c), collect())
                ->sortDesc()->take(10),
        ]);
    }

    /**
     * Different platforms spell the same challenge differently
     * ('missing_access_review' vs 'access_review_missing') — each split
     * key fragments the pattern data. Flag keys whose token sets match.
     */
    private function challengeAliases()
    {
        return Signal::whereNotNull('challenge_key')
            ->distinct()->pluck('challenge_key')
            ->groupBy(fn ($key) => collect(explode('_', (string) $key))->sort()->implode('_'))
            ->filter(fn ($group) => $group->count() > 1)
            ->map(fn ($group) => $group->sort()->values())
            ->values();
    }

    /**
     * Challenges the ecosystem is signalling but no active measure
     * addresses — risks the system cannot yet recommend against.
     */
    private function coverageGaps()
    {
        $covered = ActionMeasure::where('is_active', true)->get()->flatMap->addresses_challenges->unique();

        return Signal::whereNotNull('challenge_key')
            ->select('challenge_key', DB::raw('count(*) as signals'))
            ->selectRaw('count(distinct company_id) as companies')
            ->groupBy('challenge_key')
            ->orderByDesc('signals')
            ->get()
            ->reject(fn ($row) => $covered->contains($row->challenge_key))
            ->values();
    }

    /**
     * Model-quality check: for recommendations with a measured outcome,
     * does predicted confidence match the observed success fraction?
     * Bucketed by confidence decile.
     */
    private function calibration()
    {
        return Recommendation::whereHas('outcome')
            ->with('outcome')
            ->get()
            ->groupBy(fn ($r) => min(9, intdiv((int) $r->confidence, 10)) * 10)
            ->map(function ($bucket, $decile) {
                $successes = $bucket->filter(fn ($r) => in_array($r->outcome->result, ['success', 'partial']))->count();

                return [
                    'range' => $decile.'–'.($decile + 9).'%',
                    'count' => $bucket->count(),
                    'predicted' => round($bucket->avg('confidence'), 1),
                    'observed' => round($successes / $bucket->count() * 100, 1),
                ];
            })
            ->sortKeys()
            ->values();
    }

    /**
     * What DISAVO sees: strategic decision-making intelligence only.
     */
    public function disavo()
    {
        $outcomes = Outcome::count();

        return view('intelligence.disavo', [
            'platformsConnected' => Source::where('is_active', true)->count(),
            'companiesCovered' => Company::count(),
            'signalsProcessed' => Signal::count(),
            'recommendationsIssued' => Recommendation::count(),
            'outcomesMeasured' => $outcomes,
            'successRate' => $outcomes > 0
                ? round(Outcome::whereIn('result', ['success', 'partial'])->count() / $outcomes * 100, 1)
                : null,
            'newCompanies30d' => Company::where('created_at', '>=', now()->subDays(30))->count(),
            'signals30d' => Signal::where('occurred_at', '>=', now()->subDays(30))->count(),
            'signalsPrev30d' => Signal::whereBetween('occurred_at', [now()->subDays(60), now()->subDays(30)])->count(),
            'adoptionRate' => Recommendation::count() > 0
                ? round(Recommendation::whereIn('status', ['accepted', 'implemented'])->count() / Recommendation::count() * 100, 1)
                : null,
            'emergingRisks' => Signal::whereNotNull('challenge_key')
                ->where('occurred_at', '>=', now()->subDays(30))
                ->select('challenge_key', DB::raw('count(*) as total'))
                ->groupBy('challenge_key')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
            // Strategic health: how much of what companies report can the
            // system actually act on, and is data still flowing?
            'coverageRatio' => $this->coverageRatio(),
            'staleSources' => Source::where('is_active', true)->get()
                ->filter(fn ($s) => ! $s->signals()->where('occurred_at', '>=', now()->subDays(30))->exists())
                ->count(),
        ]);
    }

    /**
     * Share of signalled challenges that at least one active measure
     * addresses — the system's ability to answer what it sees.
     */
    private function coverageRatio(): ?float
    {
        $covered = ActionMeasure::where('is_active', true)->get()
            ->flatMap->addresses_challenges->unique();
        $signalled = Signal::whereNotNull('challenge_key')->distinct()->pluck('challenge_key');

        if ($signalled->isEmpty()) {
            return null;
        }

        return round($signalled->intersect($covered)->count() / $signalled->count() * 100, 1);
    }
}
