<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Source;
use App\Services\RecommendationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvidenceShrinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_many_attempts_at_lower_rate_beat_one_attempt_at_100(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c']);
        $thin = ActionMeasure::create(['key' => 'thin', 'name' => 'Thin']);
        $deep = ActionMeasure::create(['key' => 'deep', 'name' => 'Deep']);

        Pattern::create(['challenge_key' => 'ch', 'cohort' => 'global', 'action_measure_id' => $thin->id,
            'attempts' => 1, 'successes' => 1, 'failures' => 0, 'last_outcome_at' => now()]);
        Pattern::create(['challenge_key' => 'ch', 'cohort' => 'global', 'action_measure_id' => $deep->id,
            'attempts' => 10, 'successes' => 8, 'failures' => 2, 'last_outcome_at' => now()]);

        $recs = app(RecommendationEngine::class)->recommendFor($company, 'ch');

        $this->assertSame($deep->id, $recs->first()->action_measure_id);
    }
}
