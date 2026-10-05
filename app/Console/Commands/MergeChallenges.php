<?php

namespace App\Console\Commands;

use App\Models\ActionMeasure;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeChallenges extends Command
{
    protected $signature = 'allocore:merge-challenges {from : key to remove} {to : canonical key}';

    protected $description = 'Merge a duplicate challenge key into its canonical key across signals, recommendations, patterns and measures';

    public function handle(): int
    {
        $from = (string) $this->argument('from');
        $to = (string) $this->argument('to');

        if ($from === $to) {
            $this->error('Keys are identical.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($from, $to) {
            $signals = Signal::where('challenge_key', $from)->update(['challenge_key' => $to]);
            $recs = Recommendation::where('challenge_key', $from)->update(['challenge_key' => $to]);

            // Fold `from` patterns into `to` patterns per (measure, cohort).
            $folded = 0;
            foreach (Pattern::where('challenge_key', $from)->get() as $old) {
                $target = Pattern::firstOrNew([
                    'challenge_key' => $to,
                    'action_measure_id' => $old->action_measure_id,
                    'cohort' => $old->cohort,
                ]);
                $target->attempts = ($target->attempts ?? 0) + $old->attempts;
                $target->successes = ($target->successes ?? 0) + $old->successes;
                $target->failures = ($target->failures ?? 0) + $old->failures;
                $target->failure_reasons = collect($target->failure_reasons ?? [])
                    ->merge($old->failure_reasons ?? [])
                    ->groupBy(fn ($_, $key) => $key)
                    ->map(fn ($v) => $v->sum())
                    ->all();
                $target->last_outcome_at = collect([$target->last_outcome_at, $old->last_outcome_at])
                    ->filter()->max();
                $target->save();
                $old->delete();
                $folded++;
            }

            // Rewrite measures' challenge lists.
            $measures = 0;
            foreach (ActionMeasure::all() as $m) {
                $list = $m->addresses_challenges ?? [];
                if (in_array($from, $list, true)) {
                    $m->update(['addresses_challenges' => array_values(array_unique(array_map(
                        fn ($k) => $k === $from ? $to : $k, $list)))]);
                    $measures++;
                }
            }

            $this->info("Merged '$from' into '$to': {$signals} signals, {$recs} recommendations, {$folded} patterns folded, {$measures} measures updated.");
        });

        return self::SUCCESS;
    }
}
