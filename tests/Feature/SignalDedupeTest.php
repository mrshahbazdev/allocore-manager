<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use App\Services\SignalIngestor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalDedupeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingesting_same_signal_twice_does_not_duplicate(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $ingestor = app(SignalIngestor::class);
        $row = [
            'type' => 'risk.detected', 'challenge_key' => 'ch',
            'company' => ['external_id' => 'acme', 'name' => 'Acme'],
            'occurred_at' => '2026-10-01T10:00:00+00:00',
        ];

        $first = $ingestor->ingest($source, $row);
        $second = $ingestor->ingest($source, $row);

        $this->assertTrue($first->wasRecentlyCreated);
        $this->assertFalse($second->wasRecentlyCreated);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Signal::count());
    }

    public function test_import_accepts_external_user_id_and_skips_dupes(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $line = json_encode([
            'type' => 'risk.detected', 'challenge_key' => 'ch',
            'external_user_id' => 'user-7',
            'occurred_at' => '2026-10-01T10:00:00+00:00',
        ]);

        $this->post(route('sources.import.store', $source), ['lines' => $line]);
        $this->assertSame('user-7', Signal::first()->external_user_id);

        $response = $this->post(route('sources.import.store', $source), ['lines' => $line]);
        $response->assertSessionHas('status', fn ($s) => str_contains($s, '1 duplicate(s) skipped'));
        $this->assertSame(1, Signal::count());
    }
}
