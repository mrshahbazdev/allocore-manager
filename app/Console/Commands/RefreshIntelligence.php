<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\RecommendationEngine;
use Illuminate\Console\Command;

class RefreshIntelligence extends Command
{
    protected $signature = 'allocore:refresh {--company= : limit to one company id}';

    protected $description = 'Re-evaluate open challenges and generate fresh recommendations (the advisor loop, run on schedule).';

    public function handle(RecommendationEngine $engine): int
    {
        $companies = $this->option('company')
            ? Company::whereKey($this->option('company'))->get()
            : Company::all();

        $created = 0;
        foreach ($companies as $company) {
            $created += $engine->refreshFor($company)->count();
        }

        $this->info("Evaluated {$companies->count()} companies, {$created} new recommendation(s).");

        return self::SUCCESS;
    }
}
