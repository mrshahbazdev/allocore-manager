<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_sources_page_shows_feed_health(): void
    {
        $fresh = Source::create(['key' => 'fresh', 'name' => 'FreshCo', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $fresh->id, 'external_id' => 'c', 'name' => 'Acme']);
        Signal::create([
            'source_id' => $fresh->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'ch', 'occurred_at' => now(),
        ]);
        $old = Source::create(['key' => 'old', 'name' => 'OldCo', 'type' => 'saas_platform']);
        Signal::create([
            'source_id' => $old->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'ch', 'occurred_at' => now()->subDays(60),
        ]);

        $this->get(route('sources.index'))
            ->assertOk()
            ->assertSee('healthy')
            ->assertSee('stale');
    }
}
