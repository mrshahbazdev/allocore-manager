<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use Illuminate\Support\Collection;

class RecommendationEngine
{
    public function __construct(private CompanySimilarity $similarity) {}

    /**
     * For a company's detected challenge, recommend the action with the
     * best measured success rate among similar companies.
     *
     * Answers: "What is the best next action for this specific company?"
     *
     * @return Collection<int, Recommendation>
     */
    public function recommendFor(Company $company, string $challengeKey, ?Signal $trigger = null): Collection
    {
        $cohort = $this->similarity->cohortFor($company);

        $patterns = Pattern::query()
            ->where('challenge_key', $challengeKey)
            ->whereIn('cohort', [$cohort, 'global'])
            ->where('attempts', '>=', 1)
            ->with('actionMeasure')
            ->get()
            ->filter(fn (Pattern $p) => $p->actionMeasure?->is_active)
            ->sortByDesc(fn (Pattern $p) => $p->successRate() ?? 0);

        $existing = Recommendation::query()
            ->where('company_id', $company->id)
            ->where('challenge_key', $challengeKey)
            ->where('status', Recommendation::STATUS_PENDING)
            ->pluck('action_measure_id');

        return $patterns
            ->reject(fn (Pattern $p) => $existing->contains($p->action_measure_id))
            ->take(3)
            ->map(function (Pattern $pattern) use ($company, $challengeKey, $trigger) {
                return Recommendation::create([
                    'company_id' => $company->id,
                    'action_measure_id' => $pattern->action_measure_id,
                    'challenge_key' => $challengeKey,
                    'confidence' => $pattern->successRate(),
                    'signal_id' => $trigger?->id,
                    'status' => Recommendation::STATUS_PENDING,
                    'rationale' => [
                        'cohort' => $pattern->cohort === 'global' ? 'all companies' : 'similar companies',
                        'attempts' => $pattern->attempts,
                        'success_rate' => $pattern->successRate(),
                        'message' => sprintf(
                            '%d comparable companies implemented this measure; %s%% achieved the desired improvement.',
                            $pattern->attempts,
                            $pattern->successRate() ?? 0
                        ),
                    ],
                ]);
            })
            ->values();
    }

    /**
     * Detect open challenges for a company from its signals and generate
     * recommendations for each.
     */
    public function refreshFor(Company $company): Collection
    {
        $challenges = $company->signals()
            ->whereNotNull('challenge_key')
            ->where('occurred_at', '>=', now()->subDays(90))
            ->distinct()
            ->pluck('challenge_key');

        return $challenges->flatMap(
            fn (string $key) => $this->recommendFor($company, $key)
        );
    }
}
