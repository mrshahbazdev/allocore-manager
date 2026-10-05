<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use App\Services\RecommendationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BestNextActionTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private RecommendationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $this->company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'maturity' => 'growing']);
        $this->engine = app(RecommendationEngine::class);
    }

    public function test_best_next_action_picks_highest_confidence_pending(): void
    {
        $low = ActionMeasure::create(['key' => 'm1', 'name' => 'Low']);
        $high = ActionMeasure::create(['key' => 'm2', 'name' => 'High']);
        Recommendation::create(['company_id' => $this->company->id, 'action_measure_id' => $low->id, 'challenge_key' => 'a', 'confidence' => 40]);
        Recommendation::create(['company_id' => $this->company->id, 'action_measure_id' => $high->id, 'challenge_key' => 'b', 'confidence' => 85]);

        $this->assertEquals('High', $this->engine->bestNextAction($this->company)->actionMeasure->name);
        $this->get(route('companies.show', $this->company))->assertOk()->assertSee('Best next action');
    }

    public function test_dismissed_measures_are_not_recommended_again(): void
    {
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        Pattern::create(['challenge_key' => 'ch', 'action_measure_id' => $measure->id, 'cohort' => 'global', 'attempts' => 5, 'successes' => 4]);
        Recommendation::create([
            'company_id' => $this->company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'status' => Recommendation::STATUS_DISMISSED,
        ]);

        $this->assertCount(0, $this->engine->recommendFor($this->company, 'ch'));
    }

    public function test_stale_pending_recommendations_auto_dismiss(): void
    {
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        Recommendation::create([
            'company_id' => $this->company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'old_challenge', 'status' => 'pending',
        ]);
        // challenge has not been signaled in the last 90 days
        $this->assertEquals(1, $this->engine->expireStale($this->company));
        $this->assertEquals('dismissed', Recommendation::first()->status);
    }

    public function test_company_page_shows_cooccurring_challenges(): void
    {
        $peer = Company::create(['source_id' => $this->company->source_id, 'external_id' => 'peer']);
        foreach ([[$this->company, 'shared_ch'], [$peer, 'shared_ch'], [$peer, 'peer_only_ch']] as [$co, $ch]) {
            Signal::create([
                'source_id' => $co->source_id, 'company_id' => $co->id,
                'type' => 'risk.detected', 'challenge_key' => $ch, 'occurred_at' => now(),
            ]);
        }

        $this->get(route('companies.show', $this->company))
            ->assertOk()
            ->assertSee('Often seen together')
            ->assertSee('peer_only_ch');
    }
}
