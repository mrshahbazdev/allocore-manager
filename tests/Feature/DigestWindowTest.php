<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigestWindowTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_window_selector_counts_older_signals(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        Signal::create(['source_id' => $source->id, 'type' => 't', 'occurred_at' => now()->subDays(5)]);

        $this->get(route('digest', ['days' => 1]))->assertOk()
            ->assertSee('>0</div>', false);
        $this->get(route('digest', ['days' => 7]))->assertOk()
            ->assertSee('>1</div>', false);
    }
}
