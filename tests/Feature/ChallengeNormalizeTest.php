<?php

namespace Tests\Feature;

use App\Models\Source;
use App\Services\SignalIngestor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeNormalizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenge_key_is_normalized_at_ingest(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform', 'ingest_token' => 't']);

        $signal = app(SignalIngestor::class)->ingest($source, [
            'type' => 'risk.detected', 'challenge_key' => '  GDPR Breach ',
        ]);

        $this->assertSame('gdpr_breach', $signal->challenge_key);
    }
}
