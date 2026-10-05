<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_filters_by_source(): void
    {
        $a = Source::create(['key' => 'a', 'name' => 'Alpha', 'type' => 'saas_platform']);
        $b = Source::create(['key' => 'b', 'name' => 'Beta', 'type' => 'saas_platform']);
        Signal::create(['source_id' => $a->id, 'type' => 'x', 'external_user_id' => 'USER_A', 'payload' => [], 'occurred_at' => now()]);
        Signal::create(['source_id' => $b->id, 'type' => 'x', 'external_user_id' => 'USER_B', 'payload' => [], 'occurred_at' => now()]);

        $this->get(route('users.index'))->assertOk()->assertSee('USER_A')->assertSee('USER_B');
        $this->get(route('users.index', ['source_id' => $a->id]))->assertOk()
            ->assertSee('USER_A')->assertDontSee('USER_B');
    }
}
