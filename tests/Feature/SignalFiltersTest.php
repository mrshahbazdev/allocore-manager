<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_filters_by_source_type_challenge(): void
    {
        $a = Source::create(['key' => 'a', 'name' => 'Alpha', 'type' => 'saas_platform']);
        $b = Source::create(['key' => 'b', 'name' => 'Beta', 'type' => 'saas_platform']);

        Signal::create(['source_id' => $a->id, 'type' => 'challenge', 'challenge_key' => 'risk_a', 'external_user_id' => 'USER_ONE', 'occurred_at' => now()]);
        Signal::create(['source_id' => $b->id, 'type' => 'activity', 'challenge_key' => 'risk_b', 'external_user_id' => 'USER_TWO', 'occurred_at' => now()]);

        $this->get("/signals?source_id={$a->id}")
            ->assertOk()->assertSee('USER_ONE')->assertDontSee('USER_TWO');

        $this->get('/signals?type=activity')
            ->assertOk()->assertSee('USER_TWO')->assertDontSee('USER_ONE');

        $this->get('/signals?challenge=risk_a')
            ->assertOk()->assertSee('USER_ONE')->assertDontSee('USER_TWO');
    }
}
