<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use App\Services\RecommendationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DismissalLearningTest extends TestCase
{
    use RefreshDatabase;

    public function test_dismissed_recommendation_increments_pattern_dismissals(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        Pattern::create([
            'challenge_key' => 'ch', 'action_measure_id' => $measure->id, 'cohort' => 'global',
            'attempts' => 4, 'successes' => 4, 'failures' => 0,
        ]);
        $rec = Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'confidence' => 80,
        ]);

        $this->patch(route('recommendations.update', $rec), ['status' => 'dismissed']);

        $this->assertEquals(1, Pattern::first()->fresh()->dismissals);
    }

    public function test_dismissals_lower_recommendation_rank(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $ignored = ActionMeasure::create(['key' => 'ignored', 'name' => 'Often ignored']);
        $adopted = ActionMeasure::create(['key' => 'adopted', 'name' => 'Adopted']);

        // Same 80% success rate — but the first measure gets dismissed as often as adopted.
        Pattern::create([
            'challenge_key' => 'ch', 'action_measure_id' => $ignored->id, 'cohort' => 'global',
            'attempts' => 5, 'successes' => 4, 'failures' => 1, 'dismissals' => 5,
        ]);
        Pattern::create([
            'challenge_key' => 'ch', 'action_measure_id' => $adopted->id, 'cohort' => 'global',
            'attempts' => 5, 'successes' => 4, 'failures' => 1,
        ]);

        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'ch', 'occurred_at' => now(),
        ]);

        $engine = app(RecommendationEngine::class);
        $engine->refreshFor($company);

        $top = Recommendation::where('company_id', $company->id)->orderByDesc('confidence')->first();
        $this->assertEquals($adopted->id, $top->action_measure_id);
        // 80% * 0.5 adoption = 40 for the ignored measure
        $this->assertEquals(40, Pattern::where('action_measure_id', $ignored->id)->first()->effectiveRate());
    }
}
