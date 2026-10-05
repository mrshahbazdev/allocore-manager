<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisavoStrategicTest extends TestCase
{
    use RefreshDatabase;

    public function test_disavo_page_shows_coverage_and_feed_health(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform', 'is_active' => true]);
        ActionMeasure::create(['key' => 'm', 'name' => 'M', 'addresses_challenges' => ['ch1'], 'is_active' => true]);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'ch1', 'occurred_at' => now()]);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'ch2', 'occurred_at' => now()]);

        $this->get(route('intelligence.disavo'))
            ->assertOk()
            ->assertSee('Challenge coverage')
            ->assertSee('50%')
            ->assertSee('Feeds quiet 30d');
    }
}
