<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_challenge_page_shows_full_picture(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'Allocore', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Quarterly reviews']);
        Pattern::create([
            'challenge_key' => 'missing_access_review', 'action_measure_id' => $measure->id,
            'cohort' => 'global', 'attempts' => 4, 'successes' => 3, 'failures' => 1,
        ]);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'missing_access_review', 'occurred_at' => now(),
        ]);

        $this->get(route('challenges.show', 'missing_access_review'))
            ->assertOk()
            ->assertSee('missing_access_review')
            ->assertSee('Acme')
            ->assertSee('Quarterly reviews')
            ->assertSee('75%');
    }

    public function test_challenge_page_flags_coverage_gap(): void
    {
        $this->get(route('challenges.show', 'brand_new_challenge'))
            ->assertOk()
            ->assertSee('coverage gap');
    }
}
