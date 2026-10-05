<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Source;
use App\Services\RecommendationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoRefailTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_measure_is_not_recommended_again_for_same_company(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $failed = ActionMeasure::create(['key' => 'm_fail', 'name' => 'Failed', 'is_active' => true]);
        $good = ActionMeasure::create(['key' => 'm_good', 'name' => 'Good', 'is_active' => true]);

        foreach ([$failed, $good] as $m) {
            Pattern::create([
                'challenge_key' => 'ch', 'action_measure_id' => $m->id, 'cohort' => 'global',
                'attempts' => 5, 'successes' => 5, 'failures' => 0,
            ]);
        }

        // The failed measure was already tried here and failed.
        $rec = Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $failed->id,
            'challenge_key' => 'ch', 'status' => 'implemented', 'confidence' => 80,
        ]);
        Outcome::create(['recommendation_id' => $rec->id, 'result' => 'failure', 'measured_at' => now()]);

        $recs = app(RecommendationEngine::class)->recommendFor($company, 'ch');

        $this->assertSame([$good->id], $recs->pluck('action_measure_id')->all());
    }
}
