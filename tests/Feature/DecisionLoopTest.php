<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }

    public function test_signal_triggers_recommendation_from_learned_pattern(): void
    {
        Pattern::create([
            'challenge_key' => 'missing_access_review',
            'action_measure_id' => $this->measure->id,
            'cohort' => 'global',
            'attempts' => 4,
            'successes' => 3,
            'failures' => 1,
        ]);

        $this->post(route('signals.store'), [
            'source_id' => $this->source->id,
            'type' => 'risk.detected',
            'challenge_key' => 'missing_access_review',
            'company_external_id' => 'c-1',
            'company_name' => 'Dental Lab A',
        ])->assertRedirect(route('dashboard'));

        $rec = Recommendation::first();
        $this->assertNotNull($rec);
        $this->assertEquals(50.0, $rec->confidence);
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

        $this->post(route('recommendations.outcome', $rec), [
            'result' => 'failure',
            'failure_reason' => 'no_budget',
        ])->assertRedirect();

        $global = Pattern::where('cohort', 'global')->first();
        $this->assertEquals(1, $global->attempts);
        $this->assertEquals(1, $global->failures);
        $this->assertEquals(['no_budget' => 1], $global->failure_reasons);

        $this->assertNotNull(Pattern::where('cohort', '!=', 'global')->first());
        $this->assertEquals(Recommendation::STATUS_IMPLEMENTED, $rec->fresh()->status);
    }

    public function test_intelligence_pages_render_aggregates(): void
    {
        Company::create(['source_id' => $this->source->id, 'external_id' => 'c-3']);

        $this->get(route('intelligence.platform', $this->source))->assertOk();
        $this->get(route('intelligence.allocore'))->assertOk();
        $this->get(route('intelligence.disavo'))->assertOk();
    }
}
