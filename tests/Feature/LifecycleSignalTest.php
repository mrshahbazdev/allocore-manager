<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifecycleSignalTest extends TestCase
{
    use RefreshDatabase;

    private Source $source;

    private Company $company;

    private Recommendation $rec;

    protected function setUp(): void
    {
        parent::setUp();
        $this->source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $this->company = Company::create(['source_id' => $this->source->id, 'external_id' => 'c']);
        $measure = ActionMeasure::create(['key' => 'access_reviews', 'name' => 'Access reviews']);
        $this->rec = Recommendation::create([
            'company_id' => $this->company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'missing_access_review', 'status' => 'accepted',
        ]);
    }

    public function test_action_implemented_signal_marks_recommendation(): void
    {
        $this->post(route('signals.store'), [
            'source_id' => $this->source->id,
            'type' => 'action.implemented',
            'company_external_id' => 'c',
            'measure_key' => 'access_reviews',
        ]);

        $this->assertEquals('implemented', $this->rec->fresh()->status);
    }

    public function test_outcome_measured_signal_feeds_learning_loop(): void
    {
        $this->post(route('signals.store'), [
            'source_id' => $this->source->id,
            'type' => 'outcome.measured',
            'company_external_id' => 'c',
            'measure_key' => 'access_reviews',
            'result' => 'success',
        ]);

        $this->assertDatabaseHas('outcomes', ['recommendation_id' => $this->rec->id, 'result' => 'success']);
        $this->assertEquals(1, Pattern::where('cohort', 'global')->value('successes'));
    }
}
