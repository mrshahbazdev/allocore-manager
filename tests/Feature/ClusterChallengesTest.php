<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClusterChallengesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cluster_page_shows_common_challenges_per_cohort(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        Signal::create(['source_id' => $source->id, 'company_id' => $company->id, 'type' => 'risk.detected', 'challenge_key' => 'shared_pain', 'occurred_at' => now()]);

        $this->get(route('clusters.index'))
            ->assertOk()->assertSee('Common challenges')->assertSee('shared_pain');
    }
}
