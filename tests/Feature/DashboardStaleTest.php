<?php

namespace Tests\Feature;

use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardStaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_flags_sources_with_no_recent_signals(): void
    {
        Source::create(['key' => 'dead', 'name' => 'DeadFeed', 'type' => 'saas_platform']);

        $this->get(route('dashboard'))->assertOk()->assertSee('Quiet feeds');
    }
}
