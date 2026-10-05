<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmergingChallengesTest extends TestCase
{
    use RefreshDatabase;

    public function test_trends_page_lists_first_sighting_challenges(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'brand_new_risk', 'occurred_at' => now()->subDays(2)]);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'old_risk', 'occurred_at' => now()->subDays(3)]);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'old_risk', 'occurred_at' => now()->subDays(60)]);

        $this->get(route('trends.index'))
            ->assertOk()
            ->assertSee('Brand-new challenges')
            ->assertSee('brand_new_risk');
    }
}
