<?php

namespace App\Console\Commands;

use App\Models\Pattern;
use App\Services\LearningLoop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildPatterns extends Command
{
    protected $signature = 'allocore:rebuild-patterns';

    protected $description = 'Replay the recorded outcome stream into a fresh patterns table';

    public function handle(LearningLoop $loop): int
    {
        $replayed = DB::transaction(function () use ($loop) {
            Pattern::truncate();

            return $loop->rebuildPatterns();
        });

        $this->info("Rebuilt patterns from {$replayed} outcomes.");

        return self::SUCCESS;
    }
}
