<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_signal_detail_shows_full_payload(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $signal = Signal::create([
            'source_id' => $source->id, 'type' => 'risk.detected',
            'challenge_key' => 'ch', 'occurred_at' => now(),
            'payload' => ['severity' => 'high', 'note' => 'audit finding'],
        ]);

        $this->get(route('signals.show', $signal))
            ->assertOk()
            ->assertSee('risk.detected')
            ->assertSee('severity')
            ->assertSee('audit finding');
    }
}
