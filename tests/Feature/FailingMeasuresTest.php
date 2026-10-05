<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Pattern;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FailingMeasuresTest extends TestCase
{
    use RefreshDatabase;

    public function test_failing_card_lists_only_low_success_measures(): void
    {
        $bad = ActionMeasure::create(['key' => 'bad', 'name' => 'Bad Measure', 'addresses_challenges' => ['ch']]);
        $good = ActionMeasure::create(['key' => 'good', 'name' => 'Good Measure', 'addresses_challenges' => ['ch']]);
        Pattern::create(['challenge_key' => 'ch', 'action_measure_id' => $bad->id, 'cohort' => 'global', 'attempts' => 4, 'successes' => 0, 'failures' => 4]);
        Pattern::create(['challenge_key' => 'ch', 'action_measure_id' => $good->id, 'cohort' => 'global', 'attempts' => 4, 'successes' => 4, 'failures' => 0]);

        $this->get('/measures')
            ->assertOk()
            ->assertSee('Failing measures')
            ->assertSee('Bad Measure')
            ->assertSee('0%');
    }

    public function test_no_failing_card_when_all_healthy(): void
    {
        $this->get('/measures')->assertOk()->assertDontSee('Failing measures');
    }
}
