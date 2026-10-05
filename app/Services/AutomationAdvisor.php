<?php

namespace App\Services;

use App\Models\Outcome;
use App\Models\Process;
use App\Models\Recommendation;
use Illuminate\Support\Collection;

/**
 * Scores each decision path (challenge_key) on how far its loop runs
 * unattended: signals → recommendations → implementations → outcomes.
 * The system's own suggestion for where the Manual → Automated ladder
 * stands per challenge — the automation principle applied to itself.
 */
class AutomationAdvisor
{
    public function assess(): Collection
    {
        return Recommendation::query()
            ->select('challenge_key')
            ->selectRaw('count(*) as recommendations')
            ->selectRaw("sum(case when status = 'implemented' then 1 else 0 end) as implemented")
            ->selectRaw('sum(case when status = \'dismissed\' then 1 else 0 end) as dismissed')
            ->selectRaw('count(distinct company_id) as companies')
            ->groupBy('challenge_key')
            ->orderByDesc('recommendations')
            ->get()
            ->map(function ($row) {
                $row = $row->getAttributes();
                $outcomes = Outcome::whereHas('recommendation', fn ($q) => $q->where('challenge_key', $row['challenge_key']))->count();
                $coverage = $row['implemented'] > 0 ? $outcomes / $row['implemented'] : 0;

                $row['outcomes'] = $outcomes;
                $row['coverage'] = $coverage;
                $row['suggested_stage'] = $this->stageFor($row);
                $row['reason'] = $this->reasonFor($row);

                return $row;
            });
    }

    private function stageFor(array $row): string
    {
        if ($row['outcomes'] >= 5 && $row['coverage'] >= 0.8) {
            return Process::STAGES[3]; // automated
        }
        if ($row['coverage'] >= 0.5) {
            return Process::STAGES[2]; // semi_automated
        }
        if ($row['outcomes'] > 0) {
            return Process::STAGES[1]; // assisted
        }

        return Process::STAGES[0]; // manual
    }

    private function reasonFor(array $row): string
    {
        return match ($row['suggested_stage']) {
            'automated' => "Loop closes reliably on its own: {$row['outcomes']} measured outcomes, ".round($row['coverage'] * 100).'% implementation coverage.',
            'semi_automated' => 'Recommendations are being implemented and measured; safe to remove manual steps.',
            'assisted' => 'Outcomes are being recorded but coverage is thin — keep humans in the loop.',
            default => 'Not enough measured outcomes yet to trust automation.',
        };
    }
}
