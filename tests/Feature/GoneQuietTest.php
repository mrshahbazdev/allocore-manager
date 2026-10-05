<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use App\Services\TrendDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoneQuietTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenge_active_then_silent_is_flagged_quiet(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        Signal::create(['source_id' => $source->id, 'type' => 't', 'challenge_key' => 'faded', 'occurred_at' => now()->subDays(40)]);
        Signal::create(['source_id' => $source->id, 'type' => 't', 'challenge_key' => 'still_hot', 'occurred_at' => now()]);

        $quiet = app(TrendDetector::class)->goneQuiet();

        $this->assertSame('faded', $quiet->first()['challenge_key']);
        $this->assertFalse($quiet->contains(fn ($q) => $q['challenge_key'] === 'still_hot'));
    }
}
