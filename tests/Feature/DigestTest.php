<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_shows_last_24h_activity(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Fix it']);

        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'fresh_ch', 'occurred_at' => now(),
        ]);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'old_ch', 'occurred_at' => now()->subDays(3),
        ]);
        Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'fresh_ch', 'confidence' => 80,
        ]);

        $this->get(route('digest'))
            ->assertOk()
            ->assertSee('fresh_ch')
            ->assertSee('Acme')
            ->assertSee('Fix it')
            ->assertDontSee('old_ch');
    }
}
