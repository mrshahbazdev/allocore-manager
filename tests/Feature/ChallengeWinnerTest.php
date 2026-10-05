<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Pattern;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeWinnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenge_page_names_best_proven_measure(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'ch', 'occurred_at' => now()]);
        $weak = ActionMeasure::create(['key' => 'mw', 'name' => 'WeakOne', 'is_active' => true]);
        $strong = ActionMeasure::create(['key' => 'ms', 'name' => 'StrongOne', 'is_active' => true]);
        Pattern::create(['challenge_key' => 'ch', 'action_measure_id' => $weak->id, 'cohort' => 'global', 'attempts' => 5, 'successes' => 1, 'failures' => 4]);
        Pattern::create(['challenge_key' => 'ch', 'action_measure_id' => $strong->id, 'cohort' => 'global', 'attempts' => 5, 'successes' => 4, 'failures' => 1]);

        $this->get(route('challenges.show', 'ch'))
            ->assertOk()
            ->assertSee('Best proven measure')
            ->assertSee('StrongOne');
    }
}
