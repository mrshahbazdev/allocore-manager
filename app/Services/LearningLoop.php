<?php

namespace App\Services;

use App\Models\Outcome;
use App\Models\Pattern;
use App\Models\Recommendation;

class LearningLoop
{
    public function __construct(private CompanySimilarity $similarity) {}

    /**
     * Record a measured outcome and feed it back into pattern statistics.
     * This closes the loop: recommendation -> implementation -> outcome -> learning.
     */
    public function recordOutcome(Recommendation $recommendation, string $result, ?string $failureReason = null, ?array $metrics = null): Outcome
    {
        $outcome = Outcome::updateOrCreate(
            ['recommendation_id' => $recommendation->id],
            [
                'result' => $result,
                'failure_reason' => $failureReason,
                'metrics' => $metrics,
                'measured_at' => now(),
            ]
        );

        $recommendation->update(['status' => Recommendation::STATUS_IMPLEMENTED]);

        $this->updatePatterns($recommendation, $result, $failureReason);

        return $outcome;
    }

    private function updatePatterns(Recommendation $recommendation, string $result, ?string $failureReason): void
    {
        $cohorts = ['global', $this->similarity->cohortFor($recommendation->company)];

        foreach (array_unique($cohorts) as $cohort) {
            $pattern = Pattern::firstOrCreate(
                [
                    'challenge_key' => $recommendation->challenge_key,
                    'action_measure_id' => $recommendation->action_measure_id,
                    'cohort' => $cohort,
                ],
                ['attempts' => 0, 'successes' => 0, 'failures' => 0, 'failure_reasons' => []]
            );

            $pattern->increment('attempts');

            match ($result) {
                Outcome::RESULT_SUCCESS, Outcome::RESULT_PARTIAL => $pattern->increment('successes'),
                default => $pattern->increment('failures'),
            };

            if ($result === Outcome::RESULT_FAILURE && $failureReason) {
                $reasons = $pattern->failure_reasons ?? [];
                $reasons[$failureReason] = ($reasons[$failureReason] ?? 0) + 1;
                $pattern->update(['failure_reasons' => $reasons]);
            }
        }
    }
}
