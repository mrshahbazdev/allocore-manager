<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasurePrefillTest extends TestCase
{
    use RefreshDatabase;

    public function test_measure_create_prefills_challenge_from_query(): void
    {
        $this->get(route('measures.create', ['challenge' => 'orphan_risk']))
            ->assertOk()
            ->assertSee('value="orphan_risk"', false);
    }
}
