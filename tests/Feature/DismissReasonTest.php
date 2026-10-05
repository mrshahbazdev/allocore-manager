<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DismissReasonTest extends TestCase
{
    use RefreshDatabase;

    public function test_dismiss_with_reason_is_recorded_on_pattern_and_rationale(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c1', 'name' => 'C1']);
        $measure = ActionMeasure::create([
            'key' => 'backup', 'name' => 'Backup', 'description' => 'd',
            'addresses_challenges' => ['it_risk'], 'is_active' => true,
        ]);
        Pattern::create([
            'challenge_key' => 'it_risk', 'action_measure_id' => $measure->id, 'cohort' => 'global',
            'attempts' => 2, 'successes' => 2, 'failures' => 0,
        ]);
        $rec = Recommendation::create([
            'company_id' => $company->id,
            'action_measure_id' => $measure->id,
            'challenge_key' => 'it_risk',
            'status' => 'pending',
            'confidence' => 50,
            'rationale' => ['cohort' => 'global'],
        ]);

        $this->patch(route('recommendations.update', $rec), [
            'status' => 'dismissed',
            'reason' => 'no_budget',
        ]);

        $rec->refresh();
        $this->assertSame('no_budget', $rec->rationale['dismiss_reason']);

        $pattern = Pattern::where('cohort', 'global')->first();
        $this->assertSame(1, $pattern->dismissals);
        $this->assertSame(1, $pattern->failure_reasons['dismissed_no_budget']);
    }

    public function test_dismiss_without_reason_still_increments_dismissals(): void
    {
        $source = Source::create(['key' => 's2', 'name' => 'S2', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c2', 'name' => 'C2']);
        $measure = ActionMeasure::create([
            'key' => 'backup', 'name' => 'Backup', 'description' => 'd',
            'addresses_challenges' => ['it_risk'], 'is_active' => true,
        ]);
        Pattern::create([
            'challenge_key' => 'it_risk', 'action_measure_id' => $measure->id, 'cohort' => 'global',
            'attempts' => 2, 'successes' => 2, 'failures' => 0,
        ]);
        $rec = Recommendation::create([
            'company_id' => $company->id,
            'action_measure_id' => $measure->id,
            'challenge_key' => 'it_risk',
            'status' => 'pending',
            'confidence' => 50,
        ]);

        $this->patch(route('recommendations.update', $rec), ['status' => 'dismissed']);

        $pattern = Pattern::where('cohort', 'global')->first();
        $this->assertSame(1, $pattern->dismissals);
        $this->assertEmpty($pattern->failure_reasons);
    }
}
