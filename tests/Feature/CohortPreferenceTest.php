<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Source;
use App\Services\CompanySimilarity;
use App\Services\RecommendationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CohortPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_cohort_evidence_beats_global_within_margin(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme', 'maturity' => 'growing']);
        $cohort = app(CompanySimilarity::class)->cohortFor($company);

        $global = ActionMeasure::create(['key' => 'mg', 'name' => 'Global', 'is_active' => true]);
        $local = ActionMeasure::create(['key' => 'ml', 'name' => 'Local', 'is_active' => true]);
        Pattern::create(['challenge_key' => 'ch', 'action_measure_id' => $global->id, 'cohort' => 'global', 'attempts' => 10, 'successes' => 8, 'failures' => 2]);
        Pattern::create(['challenge_key' => 'ch', 'action_measure_id' => $local->id, 'cohort' => $cohort, 'attempts' => 10, 'successes' => 7, 'failures' => 3]);

        $recs = app(RecommendationEngine::class)->recommendFor($company, 'ch');

        $this->assertSame($local->id, $recs->first()->action_measure_id);
    }
}
