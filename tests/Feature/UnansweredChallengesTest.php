<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnansweredChallengesTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_page_flags_signalled_challenges_without_recommendation(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c1', 'name' => 'Co']);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'signal', 'challenge_key' => 'orphan_risk', 'payload' => [],
            'occurred_at' => now(),
        ]);

        $this->get(route('companies.show', $company))
            ->assertOk()
            ->assertSee('Unanswered challenges')
            ->assertSee('orphan_risk');
    }
}
