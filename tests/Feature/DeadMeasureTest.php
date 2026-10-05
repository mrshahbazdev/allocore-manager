<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeadMeasureTest extends TestCase
{
    use RefreshDatabase;

    public function test_measure_with_unsignalled_challenges_is_flagged(): void
    {
        ActionMeasure::create(['key' => 'm', 'name' => 'Ghost measure', 'addresses_challenges' => ['never_seen']]);

        $this->get(route('measures.index'))
            ->assertOk()->assertSee('no signals');
    }
}
