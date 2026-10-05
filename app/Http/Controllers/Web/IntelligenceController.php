<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

        return view('intelligence.platform', [
            'source' => $source,
            'challengeCounts' => $challengeCounts,
            'effectiveness' => $effectiveness,
            'topUsers' => $topUsers,
            'risingChallenges' => $rising,
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
        ]);
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
