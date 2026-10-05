<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutcomeOverdueTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_unmeasured_recommendation_shows_overdue_badge(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'status' => 'implemented', 'confidence' => 70,
            'updated_at' => now()->subDays(45),
        ]);

        $this->get(route('companies.show', $company))
            ->assertOk()->assertSee('outcome overdue');
    }

    public function test_recent_unmeasured_recommendation_shows_no_badge(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'status' => 'implemented', 'confidence' => 70,
        ]);

        $this->get(route('companies.show', $company))
            ->assertOk()->assertDontSee('outcome overdue');
    }
}
