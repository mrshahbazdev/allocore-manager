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
            'coverageGaps' => $this->coverageGaps(),
            'calibration' => $this->calibration(),
        ]);
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
        ]);
    }
}
