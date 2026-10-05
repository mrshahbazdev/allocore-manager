<?php

namespace App\Console\Commands;

use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Signal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The ecosystem briefing, unattended — the same numbers the /digest
 * page shows, written to the log so the morning read is push, not pull.
 */
class DailyDigest extends Command
{
    protected $signature = 'allocore:digest {--days=1 : window in days}';

    protected $description = 'Print the last-N-days ecosystem digest (signals, recommendations, outcomes, open work).';

    public function handle(): int
    {
        $since = now()->subDays((int) $this->option('days'));

        $signals = Signal::where('occurred_at', '>=', $since)->count();
        $topChallenges = Signal::where('occurred_at', '>=', $since)
            ->whereNotNull('challenge_key')
            ->select('challenge_key', DB::raw('count(*) as total'))
            ->groupBy('challenge_key')->orderByDesc('total')->limit(5)->get();
        $newRecs = Recommendation::where('created_at', '>=', $since)->count();
        $newOutcomes = Outcome::where('measured_at', '>=', $since)->count();
        $pending = Recommendation::where('status', 'pending')->count();
        $unmeasured = Recommendation::where('status', 'implemented')->whereDoesntHave('outcome')->count();

        $this->info("Allocore digest — last {$this->option('days')}d (since {$since->toDateTimeString()})");
        $this->line("  signals: {$signals} | recommendations issued: {$newRecs} | outcomes measured: {$newOutcomes}");
        $this->line("  open decisions: {$pending} pending | {$unmeasured} implemented awaiting outcome");

        if ($topChallenges->isNotEmpty()) {
            $this->line('  top challenges: '.$topChallenges->map(fn ($c) => "{$c->challenge_key} ({$c->total})")->implode(', '));
        }

        return self::SUCCESS;
    }
}
