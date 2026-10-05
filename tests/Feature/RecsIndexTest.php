<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendations_index_lists_and_filters(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Measure M']);
        Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'status' => 'pending', 'confidence' => 60,
        ]);
        Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch2', 'status' => 'dismissed', 'confidence' => 40,
        ]);

        $this->get(route('recommendations.index'))->assertOk()->assertSee('Acme');
        $this->get(route('recommendations.index', ['status' => 'pending']))
            ->assertOk()->assertSee('ch')->assertDontSee('>ch2<');
    }
}
