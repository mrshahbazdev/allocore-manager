<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendation_detail_shows_rationale_and_outcome(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'My Measure']);
        $rec = Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'status' => 'implemented', 'confidence' => 75,
            'rationale' => ['message' => 'test rationale'],
        ]);
        Outcome::create(['recommendation_id' => $rec->id, 'result' => 'success', 'measured_at' => now()]);

        $this->get(route('recommendations.show', $rec))
            ->assertOk()
            ->assertSee('My Measure')
            ->assertSee('test rationale')
            ->assertSee('success');
    }
}
