<?php

namespace Tests\Feature;

use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_signal_form_records_signal_and_upserts_company(): void
    {
        $source = Source::create(['key' => 'allocore-de', 'name' => 'Allocore', 'type' => 'saas_platform']);

        $response = $this->post(route('signals.store'), [
            'source_id' => $source->id,
            'type' => 'risk.detected',
            'challenge_key' => 'missing_access_review',
            'company_external_id' => 'acme-1',
            'company_name' => 'Acme GmbH',
            'company_industry' => 'dental',
            'company_maturity' => 'growing',
            'company_situation' => 'compliance_backlog, no_it_team',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('companies', [
            'source_id' => $source->id,
            'external_id' => 'acme-1',
            'name' => 'Acme GmbH',
            'maturity' => 'growing',
        ]);
        $this->assertDatabaseHas('signals', ['type' => 'risk.detected', 'challenge_key' => 'missing_access_review']);
    }

    public function test_dashboard_and_signal_form_render(): void
    {
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('signals.create'))->assertOk();
    }
}
