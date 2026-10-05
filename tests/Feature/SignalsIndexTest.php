<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_signals_index_lists_signals(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'Allocore', 'type' => 'saas_platform']);
        Signal::create([
            'source_id' => $source->id, 'type' => 'risk.detected',
            'challenge_key' => 'ch', 'occurred_at' => now(),
        ]);

        $this->get(route('signals.index'))
            ->assertOk()->assertSee('risk.detected')->assertSee('Allocore');
    }
}
