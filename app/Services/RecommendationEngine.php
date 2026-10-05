<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Outcome;
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
            // Evidence from companies like this one outranks ecosystem-wide
            // evidence, unless the global pattern is dramatically stronger.
            ->sortByDesc(fn (Pattern $p) => ($p->effectiveRate() ?? 0)
                + ($p->cohort === $cohort ? 15 : 0));

        // Never re-issue a measure that is pending, was dismissed, or
        // already failed on this challenge for this company — measured
        // failure is the strongest "don't recommend again" signal there is.
        $existing = Recommendation::query()
            ->where('company_id', $company->id)
            ->where('challenge_key', $challengeKey)
            ->where(function ($q) {
                $q->whereIn('status', [Recommendation::STATUS_PENDING, Recommendation::STATUS_DISMISSED])
                    ->orWhereHas('outcome', fn ($o) => $o->where('result', Outcome::RESULT_FAILURE));
            })
            ->pluck('action_measure_id');

        return $patterns
            ->reject(fn (Pattern $p) => $existing->contains($p->action_measure_id))
            ->take(3)
            ->map(function (Pattern $pattern) use ($company, $challengeKey, $trigger) {
                return Recommendation::create([
                    'company_id' => $company->id,
                    'action_measure_id' => $pattern->action_measure_id,
                    'challenge_key' => $challengeKey,
                    'confidence' => $pattern->effectiveRate(),
                    'signal_id' => $trigger?->id,
                    'status' => Recommendation::STATUS_PENDING,
                    'rationale' => [
                        'cohort' => $pattern->cohort === 'global' ? 'all companies' : 'similar companies',
                        'attempts' => $pattern->attempts,
                        'success_rate' => $pattern->successRate(),
                        'evidence_age_days' => $pattern->last_outcome_at?->diffInDays(now()),
                        'dismissals' => $pattern->dismissals,
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

        $created = $challenges->flatMap(
            fn (string $key) => $this->recommendFor($company, $key)
        );

        $this->expireStale($company, $challenges);

        return $created;
    }

    /**
     * Auto-dismiss pending recommendations whose challenge has gone quiet —
     * stale advice is worse than no advice.
     */
    public function expireStale(Company $company, ?Collection $activeChallenges = null, int $quietDays = 90): int
    {
        $active = $activeChallenges ?? $company->signals()
            ->whereNotNull('challenge_key')
            ->where('occurred_at', '>=', now()->subDays($quietDays))
            ->distinct()
            ->pluck('challenge_key');

        return $company->recommendations()
            ->where('status', Recommendation::STATUS_PENDING)
            ->whereNotIn('challenge_key', $active)
            ->update(['status' => Recommendation::STATUS_DISMISSED]);
    }

    /**
     * The one question the system exists to answer: the best next action
     * for this specific company right now.
     */
    public function bestNextAction(Company $company): ?Recommendation
    {
        return $company->recommendations()
            ->with('actionMeasure')
            ->where('status', Recommendation::STATUS_PENDING)
            ->orderByDesc('confidence')
            ->first();
    }
}
