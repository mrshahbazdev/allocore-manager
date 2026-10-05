<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Source;
use App\Services\LearningLoop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RebuildPatternsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rebuild_replays_outcomes_into_patterns(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'maturity' => 'growing']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        $rec = Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'status' => 'implemented',
        ]);
        $loop = app(LearningLoop::class);
        $loop->recordOutcome($rec, 'success');

        Pattern::truncate();
        $this->assertSame(0, Pattern::count());

        $this->artisan('allocore:rebuild-patterns')->assertSuccessful();

        $global = Pattern::where('cohort', 'global')->first();
        $this->assertSame(1, $global->attempts);
        $this->assertSame(1, $global->successes);
    }
}
