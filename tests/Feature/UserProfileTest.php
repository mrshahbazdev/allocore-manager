<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_page_shows_stats_and_top_challenge(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        Signal::create(['source_id' => $source->id, 'company_id' => $company->id, 'external_user_id' => 'u1', 'type' => 'risk.detected', 'challenge_key' => 'pain', 'occurred_at' => now()]);

        $this->get(route('users.show', 'u1'))
            ->assertOk()
            ->assertSee('Companies touched')
            ->assertSee('pain');
    }
}
