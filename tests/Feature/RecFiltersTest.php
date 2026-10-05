<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_by_challenge_and_company(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $c1 = Company::create(['source_id' => $source->id, 'external_id' => 'c1', 'name' => 'Alpha']);
        $c2 = Company::create(['source_id' => $source->id, 'external_id' => 'c2', 'name' => 'Beta']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Do thing', 'addresses_challenges' => ['risk_a', 'risk_b']]);

        Recommendation::create(['company_id' => $c1->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'risk_a', 'status' => 'pending']);
        Recommendation::create(['company_id' => $c2->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'risk_b', 'status' => 'pending']);

        $this->get('/recommendations?challenge=risk_a')
            ->assertOk()->assertSee('risk_a')
            ->assertSee('Alpha')->assertDontSee('Beta');

        $this->get("/recommendations?company_id={$c2->id}")
            ->assertOk()->assertSee('Beta')->assertDontSee('Alpha');

        $this->get('/recommendations?status=pending&challenge=risk_b')
            ->assertOk()->assertSee('Beta')->assertDontSee('Alpha');
    }
}
