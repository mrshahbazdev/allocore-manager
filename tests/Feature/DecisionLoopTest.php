<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DecisionLoopTest extends TestCase
{
    use RefreshDatabase;

    private Source $source;

    private ActionMeasure $measure;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = Source::create(['key' => 'ct-de', 'name' => 'ComplianceTermine', 'type' => 'saas_platform']);
        $this->measure = ActionMeasure::create([
            'key' => 'access_reviews',
            'name' => 'Implement quarterly access reviews',
            'addresses_challenges' => ['missing_access_review'],
        ]);
        Sanctum::actingAs(User::factory()->create());
    }

    public function test_signal_triggers_recommendation_from_learned_pattern(): void
    {
        // Prior learning: measure worked for 3 of 4 similar companies.
        Pattern::create([
            'challenge_key' => 'missing_access_review',
            'action_measure_id' => $this->measure->id,
            'cohort' => 'global',
            'attempts' => 4,
            'successes' => 3,
            'failures' => 1,
        ]);

        $response = $this->withToken($this->source->ingest_token)->postJson('/api/v1/signals', [
            'type' => 'risk.detected',
            'challenge_key' => 'missing_access_review',
            'company' => ['external_id' => 'c-1', 'name' => 'Dental Lab A'],
        ]);

        $response->assertCreated();
        $rec = Recommendation::first();
        $this->assertNotNull($rec);
        $this->assertEquals(75.0, $rec->confidence);
        $this->assertStringContainsString('75%', $rec->rationale['message']);
    }

    public function test_outcome_recording_closes_the_learning_loop(): void
    {
        $company = Company::create([
            'source_id' => $this->source->id,
            'external_id' => 'c-2',
            'maturity' => 'growing',
            'situation' => ['compliance_backlog'],
        ]);

        $rec = Recommendation::create([
            'company_id' => $company->id,
            'action_measure_id' => $this->measure->id,
            'challenge_key' => 'missing_access_review',
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/recommendations/{$rec->id}/outcome", [
            'result' => 'failure',
            'failure_reason' => 'no_budget',
        ]);

        $response->assertCreated();

        // Learning: both the global and the company cohort patterns updated.
        $global = Pattern::where('cohort', 'global')->first();
        $this->assertEquals(1, $global->attempts);
        $this->assertEquals(1, $global->failures);
        $this->assertEquals(['no_budget' => 1], $global->failure_reasons);

        $cohort = Pattern::where('cohort', '!=', 'global')->first();
        $this->assertNotNull($cohort);
        $this->assertEquals(Recommendation::STATUS_IMPLEMENTED, $rec->fresh()->status);
    }

    public function test_role_intelligence_endpoints_return_aggregates(): void
    {
        Company::create(['source_id' => $this->source->id, 'external_id' => 'c-3']);

        $this->getJson("/api/v1/intelligence/platform/{$this->source->id}")
            ->assertOk()
            ->assertJsonStructure(['source', 'common_challenges', 'recommendation_effectiveness']);

        $this->getJson('/api/v1/intelligence/allocore')
            ->assertOk()
            ->assertJsonStructure(['companies', 'signals', 'overall_success_rate', 'patterns']);

        $this->getJson('/api/v1/intelligence/disavo')
            ->assertOk()
            ->assertJsonStructure(['ecosystem', 'performance', 'emerging_risks']);
    }
}
