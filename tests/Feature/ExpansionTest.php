<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Process;
use App\Models\Signal;
use App\Models\Source;
use App\Services\TrendDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpansionTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_and_measure_admin_pages_work(): void
    {
        $this->get(route('sources.index'))->assertOk();
        $this->post(route('sources.store'), [
            'key' => 'crm-system', 'name' => 'CRM', 'type' => 'system',
        ])->assertRedirect(route('sources.index'));
        $this->assertDatabaseHas('sources', ['key' => 'crm-system']);

        $this->post(route('measures.store'), [
            'key' => 'mfa_rollout', 'name' => 'MFA rollout',
            'addresses_challenges' => 'weak_auth, phishing_risk',
        ])->assertRedirect(route('measures.index'));
        $this->assertDatabaseHas('action_measures', ['key' => 'mfa_rollout']);
    }

    public function test_trend_detector_flags_rising_challenges(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c']);

        Signal::create(['source_id' => $source->id, 'company_id' => $company->id, 'type' => 'risk.detected', 'challenge_key' => 'old_issue', 'occurred_at' => now()->subDays(50)]);
        foreach ([3, 5, 8] as $d) {
            Signal::create(['source_id' => $source->id, 'company_id' => $company->id, 'type' => 'risk.detected', 'challenge_key' => 'cyber_risk', 'occurred_at' => now()->subDays($d)]);
        }

        $trends = app(TrendDetector::class)->detect(30);
        $rising = $trends->firstWhere('challenge_key', 'cyber_risk');
        $this->assertNotNull($rising);
        $this->assertEquals('rising', $rising['direction']);
        $this->assertEquals(3, $rising['recent']);
        $this->assertEquals(0, $rising['previous']);
    }

    public function test_process_advances_through_automation_stages(): void
    {
        $this->post(route('processes.store'), [
            'name' => 'Signal triage', 'scope' => 'ecosystem',
        ])->assertRedirect();

        $process = Process::first();
        $this->assertEquals('manual', $process->automation_stage);

        $this->post(route('processes.advance', $process), [
            'stage' => 'semi_automated', 'note' => 'rules engine live',
        ])->assertRedirect();

        $this->assertEquals('semi_automated', $process->fresh()->automation_stage);
        $this->assertDatabaseHas('process_assessments', [
            'process_id' => $process->id, 'from_stage' => 'manual', 'to_stage' => 'semi_automated',
        ]);
    }

    public function test_clusters_page_groups_companies_by_cohort(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        Company::create(['source_id' => $source->id, 'external_id' => 'a', 'maturity' => 'growing', 'situation' => ['x']]);
        Company::create(['source_id' => $source->id, 'external_id' => 'b', 'maturity' => 'growing', 'situation' => ['x']]);
        Company::create(['source_id' => $source->id, 'external_id' => 'c', 'maturity' => 'early', 'situation' => ['y']]);

        $this->get(route('clusters.index'))->assertOk()->assertSee('growing:x')->assertSee('early:y');
    }
}
