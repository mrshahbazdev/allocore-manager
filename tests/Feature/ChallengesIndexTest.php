<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengesIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenges_index_shows_coverage(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        ActionMeasure::create([
            'key' => 'm', 'name' => 'M', 'addresses_challenges' => ['covered_ch'], 'is_active' => true,
        ]);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'covered_ch', 'occurred_at' => now()]);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'uncovered_ch', 'occurred_at' => now()]);

        $this->get(route('challenges.index'))
            ->assertOk()
            ->assertSee('covered_ch')
            ->assertSee('uncovered_ch')
            ->assertSee('covered')
            ->assertSee('gap');
    }
}
