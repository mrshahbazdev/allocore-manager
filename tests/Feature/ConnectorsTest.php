<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnectorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_jsonl_import_ingests_signals_and_generates_recommendations(): void
    {
        $source = Source::create(['key' => 'ct-de', 'name' => 'CT', 'type' => 'saas_platform']);
        $measure = ActionMeasure::create(['key' => 'access_reviews', 'name' => 'Access reviews']);
        Pattern::create([
            'challenge_key' => 'missing_access_review', 'action_measure_id' => $measure->id,
            'cohort' => 'global', 'attempts' => 4, 'successes' => 3,
        ]);

        $lines = implode("\n", [
            '{"type":"risk.detected","challenge_key":"missing_access_review","company":{"external_id":"c-1","name":"Lab A","maturity":"growing"}}',
            '{"type":"risk.detected","challenge_key":"missed_deadlines","company":{"external_id":"c-2","name":"Lab B"}}',
            'not-json',
        ]);

        $this->post(route('sources.import.store', $source), ['lines' => $lines])
            ->assertRedirect(route('sources.index'));

        $this->assertDatabaseCount('signals', 2);
        $this->assertDatabaseCount('companies', 2);
        $this->assertGreaterThan(0, Recommendation::count());
    }

    public function test_refresh_command_regenerates_recommendations(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $measure = ActionMeasure::create(['key' => 'access_reviews', 'name' => 'Access reviews']);
        Pattern::create([
            'challenge_key' => 'missing_access_review', 'action_measure_id' => $measure->id,
            'cohort' => 'global', 'attempts' => 2, 'successes' => 2,
        ]);

        Signal::create([
            'source_id' => $source->id,
            'company_id' => Company::create(['source_id' => $source->id, 'external_id' => 'c'])->id,
            'type' => 'risk.detected', 'challenge_key' => 'missing_access_review',
            'occurred_at' => now()->subDays(3),
        ]);

        $this->artisan('allocore:refresh')->assertSuccessful();
        $this->assertEquals(1, Recommendation::count());
    }

    public function test_measure_detail_shows_cohort_effectiveness(): void
    {
        $measure = ActionMeasure::create(['key' => 'access_reviews', 'name' => 'Access reviews']);
        Pattern::create([
            'challenge_key' => 'missing_access_review', 'action_measure_id' => $measure->id,
            'cohort' => 'growing', 'attempts' => 4, 'successes' => 3,
        ]);

        $this->get(route('measures.show', $measure))
            ->assertOk()->assertSee('growing')->assertSee('75%');
    }
}
