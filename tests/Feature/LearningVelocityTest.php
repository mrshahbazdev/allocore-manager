<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningVelocityTest extends TestCase
{
    use RefreshDatabase;

    public function test_allocore_page_shows_learning_velocity(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        $rec = Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'status' => 'implemented', 'confidence' => 70,
        ]);
        Outcome::create(['recommendation_id' => $rec->id, 'result' => 'success', 'measured_at' => now()]);

        $this->get(route('intelligence.allocore'))
            ->assertOk()
            ->assertSee('Outcomes this week')
            ->assertSee('Outcomes prior week');
    }
}
