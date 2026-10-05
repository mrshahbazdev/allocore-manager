<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class IntelligenceController extends Controller
{
    /**
     * What a company sees: actionable intelligence, never raw data.
     */
    public function company(Company $company): JsonResponse
    {
        $recommendations = $company->recommendations()
            ->with('actionMeasure:key,name,description')
            ->where('status', Recommendation::STATUS_PENDING)
            ->latest()
            ->get()
            ->map(fn (Recommendation $r) => [
                'id' => $r->id,
                'challenge' => $r->challenge_key,
                'action' => $r->actionMeasure?->name,
                'description' => $r->actionMeasure?->description,
                'confidence' => $r->confidence,
                'why' => $r->rationale['message'] ?? null,
            ]);

        return response()->json([
            'company' => $company->only('id', 'name', 'industry', 'maturity'),
            'recommended_next_actions' => $recommendations,
        ]);
    }

    /**
     * What a platform manager sees: product intelligence for their platform.
     */
    public function platform(Source $source): JsonResponse
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

        return response()->json([
            'source' => $source->only('key', 'name'),
            'common_challenges' => $challengeCounts,
            'recommendation_effectiveness' => $effectiveness,
        ]);
    }

    /**
     * What the Allocore Manager team sees: learning-system quality metrics.
     */
    public function allocore(): JsonResponse
    {
        $patterns = Pattern::with('actionMeasure:key,name')
            ->orderByDesc('attempts')
            ->limit(50)
            ->get()
            ->map(fn (Pattern $p) => [
                'challenge_key' => $p->challenge_key,
                'action' => $p->actionMeasure?->name,
                'cohort' => $p->cohort,
                'attempts' => $p->attempts,
                'success_rate' => $p->successRate(),
                'top_failure_reasons' => collect($p->failure_reasons ?? [])->sortDesc()->take(3),
            ]);

        return response()->json([
            'companies' => Company::count(),
            'signals' => Signal::count(),
            'recommendations' => Recommendation::count(),
            'outcomes' => Outcome::count(),
            'overall_success_rate' => Outcome::count() > 0
                ? round(Outcome::whereIn('result', ['success', 'partial'])->count() / Outcome::count() * 100, 1)
                : null,
            'patterns' => $patterns,
        ]);
    }

    /**
     * What DISAVO sees: strategic decision-making intelligence only.
     */
    public function disavo(): JsonResponse
    {
        $activeSources = Source::where('is_active', true)->count();
        $outcomes = Outcome::count();

        $trending = Signal::whereNotNull('challenge_key')
            ->where('occurred_at', '>=', now()->subDays(30))
            ->select('challenge_key', DB::raw('count(*) as total'))
            ->groupBy('challenge_key')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json([
            'ecosystem' => [
                'platforms_connected' => $activeSources,
                'companies_covered' => Company::count(),
                'signals_processed' => Signal::count(),
            ],
            'performance' => [
                'recommendations_issued' => Recommendation::count(),
                'outcomes_measured' => $outcomes,
                'success_rate' => $outcomes > 0
                    ? round(Outcome::whereIn('result', ['success', 'partial'])->count() / $outcomes * 100, 1)
                    : null,
            ],
            'emerging_risks' => $trending,
        ]);
    }
}
