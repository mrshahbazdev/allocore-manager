<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeeperIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_page_shows_user_and_trend_intelligence(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c']);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'external_user_id' => 'user-9', 'type' => 'risk.detected',
            'challenge_key' => 'cyber_risk', 'occurred_at' => now()->subDays(3),
        ]);

        $this->get(route('intelligence.platform', $source))
            ->assertOk()->assertSee('user-9')->assertSee('cyber_risk');
    }

    public function test_platform_page_shows_company_health_rows(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme GmbH']);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'backup_risk',
            'occurred_at' => now(),
        ]);

        $this->get(route('intelligence.platform', $source))
            ->assertOk()
            ->assertSee('Company health')
            ->assertSee('Acme GmbH')
            ->assertSee('backup_risk');
    }

    public function test_disavo_page_shows_growth_indicators(): void
    {
        $this->get(route('intelligence.disavo'))
            ->assertOk()->assertSee('Growth indicators')->assertSee('New companies');
    }

    public function test_allocore_page_surfaces_challenges_with_no_measure(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c']);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'orphan_risk',
            'occurred_at' => now(),
        ]);
        ActionMeasure::create(['key' => 'm', 'name' => 'M', 'addresses_challenges' => ['covered_risk']]);

        $this->get(route('intelligence.allocore'))
            ->assertOk()
            ->assertSee('Coverage gaps')
            ->assertSee('orphan_risk');
    }
}
