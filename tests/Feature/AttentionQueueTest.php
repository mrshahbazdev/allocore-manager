<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttentionQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_queues_pending_decisions_and_missing_outcomes(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Do the thing']);

        Recommendation::create(['company_id' => $company->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'ch', 'status' => 'pending']);
        Recommendation::create(['company_id' => $company->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'ch2', 'status' => 'implemented']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Decisions waiting')
            ->assertSee('Outcomes missing')
            ->assertSee('Do the thing');
    }
}
