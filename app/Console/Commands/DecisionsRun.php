<?php

namespace App\Console\Commands;

use App\Support\DecisionEngine;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('decisions:run')]
#[Description('Scan signals, detect patterns and (re)generate recommendations')]
class DecisionsRun extends Command
{
    public function handle(DecisionEngine $engine): int
    {
        $created = $engine->run();
        $this->info("{$created} new recommendation(s) created.");

        return self::SUCCESS;
    }
}
