<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomationAdvisorTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecs(string $challenge, int $implemented, int $outcomes): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);

        foreach (range(1, $implemented) as $i) {
            $rec = Recommendation::create([
                'company_id' => $company->id, 'action_measure_id' => $measure->id,
                'challenge_key' => $challenge, 'status' => 'implemented',
            ]);
            if ($i <= $outcomes) {
                Outcome::create(['recommendation_id' => $rec->id, 'result' => 'success', 'measured_at' => now()]);
            }
        }
    }

    public function test_challenge_with_full_coverage_suggests_automated(): void
    {
        $this->makeRecs('mature_challenge', 6, 6);

        $this->get(route('processes.index'))
            ->assertOk()
            ->assertSee('mature_challenge')
            ->assertSee('automated');
    }

    public function test_challenge_without_outcomes_stays_manual(): void
    {
        $this->makeRecs('new_challenge', 2, 0);

        $this->get(route('processes.index'))
            ->assertOk()
            ->assertSee('new_challenge')
            ->assertSee('manual');
    }
}
