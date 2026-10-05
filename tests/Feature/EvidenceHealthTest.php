<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Pattern;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvidenceHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_allocore_page_shows_evidence_freshness_bands(): void
    {
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        Pattern::create(['challenge_key' => 'a', 'action_measure_id' => $measure->id, 'cohort' => 'global', 'attempts' => 1, 'last_outcome_at' => now()]);
        Pattern::create(['challenge_key' => 'b', 'action_measure_id' => $measure->id, 'cohort' => 'global', 'attempts' => 1, 'last_outcome_at' => now()->subDays(300)]);
        Pattern::create(['challenge_key' => 'c', 'action_measure_id' => $measure->id, 'cohort' => 'global', 'attempts' => 1, 'last_outcome_at' => null]);

        $this->get(route('intelligence.allocore'))
            ->assertOk()
            ->assertSee('Patterns fresh')
            ->assertSee('Patterns aging/stale')
            ->assertSee('Patterns no outcomes');
    }
}
