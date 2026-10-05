<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutcomeFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_outcomes_index_filters_by_result_and_challenge(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c1', 'name' => 'Co']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M', 'addresses_challenges' => ['x'], 'is_active' => true]);
        $ok = Recommendation::create(['company_id' => $company->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'good_key', 'status' => 'implemented', 'confidence' => 80]);
        $bad = Recommendation::create(['company_id' => $company->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'bad_key', 'status' => 'implemented', 'confidence' => 80]);
        Outcome::create(['recommendation_id' => $ok->id, 'result' => 'success', 'measured_at' => now()]);
        Outcome::create(['recommendation_id' => $bad->id, 'result' => 'failure', 'failure_reason' => 'oops', 'measured_at' => now()]);

        $this->get(route('outcomes.index'))->assertOk()
            ->assertSee('good_key')->assertSee('bad_key');

        // Filter to failures: only the failure row's reason shows.
        $this->get(route('outcomes.index', ['result' => 'failure']))->assertOk()
            ->assertSee('oops');

        // Filter by challenge: 'oops' belongs to bad_key only — use failure_reason marker.
        $this->get(route('outcomes.index', ['challenge' => 'good_key']))->assertOk()
            ->assertDontSee('oops');
    }
}
