<?php

namespace App\Console\Commands;

use App\Models\Signal;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('data:prune {--days=180 : Keep signals newer than this many days}')]
#[Description('Delete signals older than the retention window')]
class PruneOldData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = Signal::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Pruned {$deleted} signals older than {$days} days.");

        return self::SUCCESS;
    }
}
