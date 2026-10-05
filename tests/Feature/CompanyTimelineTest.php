<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_timeline_shows_signals_recommendations_outcomes(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'TestSrc', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Do thing', 'addresses_challenges' => ['cash_flow']]);

        Signal::create([
            'source_id' => $source->id,
            'company_id' => $company->id,
            'type' => 'challenge',
            'challenge_key' => 'cash_flow',
            'occurred_at' => now()->subDays(3),
        ]);
        $rec = Recommendation::create([
            'company_id' => $company->id,
            'action_measure_id' => $measure->id,
            'challenge_key' => 'cash_flow',
            'status' => 'implemented',
            'confidence' => 80,
        ]);
        Outcome::create([
            'recommendation_id' => $rec->id,
            'result' => 'success',
            'measured_at' => now()->subDay(),
        ]);

        $this->get("/companies/{$company->id}/timeline")
            ->assertOk()
            ->assertSee('signal')
            ->assertSee('recommendation')
            ->assertSee('outcome')
            ->assertSee('success')
            ->assertSee('cash_flow');
    }

    public function test_timeline_empty_for_quiet_company(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'TestSrc', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'quiet']);

        $this->get("/companies/{$company->id}/timeline")
            ->assertOk()
            ->assertSee('No activity recorded yet');
    }
}
