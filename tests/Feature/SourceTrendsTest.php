<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceTrendsTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_page_shows_per_source_trends_only(): void
    {
        $a = Source::create(['key' => 'a', 'name' => 'PlatformA', 'type' => 'saas_platform']);
        $b = Source::create(['key' => 'b', 'name' => 'PlatformB', 'type' => 'saas_platform']);

        foreach (range(1, 4) as $i) {
            Signal::create(['source_id' => $a->id, 'type' => 'challenge', 'challenge_key' => 'a_risk', 'occurred_at' => now()->subDays(5)]);
        }
        Signal::create(['source_id' => $a->id, 'type' => 'challenge', 'challenge_key' => 'a_risk', 'occurred_at' => now()->subDays(45)]);
        foreach (range(1, 6) as $i) {
            Signal::create(['source_id' => $b->id, 'type' => 'challenge', 'challenge_key' => 'b_risk', 'occurred_at' => now()->subDays(5)]);
        }

        $this->get("/sources/{$a->id}")
            ->assertOk()
            ->assertSee('a_risk')
            ->assertSee('rising')
            ->assertDontSee('b_risk');
    }
}
