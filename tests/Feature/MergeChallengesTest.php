<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Pattern;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MergeChallengesTest extends TestCase
{
    use RefreshDatabase;

    public function test_merge_rewrites_and_folds_everything(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M', 'addresses_challenges' => ['old_key', 'keep']]);
        Signal::create(['source_id' => $source->id, 'type' => 't', 'challenge_key' => 'old_key', 'occurred_at' => now()]);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        Recommendation::create(['company_id' => $company->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'old_key', 'status' => 'pending', 'confidence' => 50]);
        Pattern::create(['challenge_key' => 'old_key', 'action_measure_id' => $measure->id, 'cohort' => 'global', 'attempts' => 3, 'successes' => 2, 'failures' => 1]);
        Pattern::create(['challenge_key' => 'new_key', 'action_measure_id' => $measure->id, 'cohort' => 'global', 'attempts' => 2, 'successes' => 1, 'failures' => 1]);

        $this->artisan('allocore:merge-challenges', ['from' => 'old_key', 'to' => 'new_key'])->assertExitCode(0);

        $this->assertDatabaseMissing('signals', ['challenge_key' => 'old_key']);
        $this->assertDatabaseHas('signals', ['challenge_key' => 'new_key']);
        $this->assertDatabaseHas('recommendations', ['challenge_key' => 'new_key']);
        $this->assertDatabaseMissing('patterns', ['challenge_key' => 'old_key']);
        $folded = Pattern::where(['challenge_key' => 'new_key', 'cohort' => 'global'])->sole();
        $this->assertSame(5, $folded->attempts);
        $this->assertSame(3, $folded->successes);
        $this->assertEqualsCanonicalizing(['new_key', 'keep'], $measure->fresh()->addresses_challenges);
    }
}
