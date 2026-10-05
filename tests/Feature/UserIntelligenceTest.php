<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_and_detail_pages(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Lab']);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'external_user_id' => 'user-7', 'type' => 'risk.detected',
            'challenge_key' => 'ch', 'occurred_at' => now(),
        ]);
        Recommendation::create([
            'company_id' => $company->id,
            'action_measure_id' => ActionMeasure::create(['key' => 'm', 'name' => 'Measure X'])->id,
            'challenge_key' => 'ch', 'confidence' => 80,
        ]);

        $this->get(route('users.index'))->assertOk()->assertSee('user-7');
        $this->get(route('users.show', 'user-7'))
            ->assertOk()->assertSee('Measure X')->assertSee('Lab');
    }
}
