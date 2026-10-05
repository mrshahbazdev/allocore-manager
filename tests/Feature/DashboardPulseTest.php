<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPulseTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_weekly_activity_pulse(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        Signal::create(['source_id' => $source->id, 'type' => 't', 'occurred_at' => now()]);

        $this->get(route('dashboard'))
            ->assertOk()->assertSee('Activity pulse');
    }
}
