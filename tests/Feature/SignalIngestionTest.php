<?php

namespace Tests\Feature;

use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_can_push_a_signal_and_company_is_upserted(): void
    {
        $source = Source::create(['key' => 'allocore-de', 'name' => 'Allocore', 'type' => 'saas_platform']);

        $response = $this->withToken($source->ingest_token)->postJson('/api/v1/signals', [
            'type' => 'risk.detected',
            'challenge_key' => 'missing_access_review',
            'company' => [
                'external_id' => 'acme-1',
                'name' => 'Acme GmbH',
                'industry' => 'dental',
                'maturity' => 'growing',
                'situation' => ['compliance_backlog', 'no_it_team'],
            ],
        ]);

        $response->assertCreated()->assertJsonStructure(['signal_id', 'recommendations']);

        $this->assertDatabaseHas('companies', [
            'source_id' => $source->id,
            'external_id' => 'acme-1',
            'name' => 'Acme GmbH',
        ]);
        $this->assertDatabaseHas('signals', ['type' => 'risk.detected', 'challenge_key' => 'missing_access_review']);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->withToken('bad-token')
            ->postJson('/api/v1/signals', ['type' => 'risk.detected'])
            ->assertUnauthorized();
    }
}
