<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeAliasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_allocore_page_flags_lookalike_challenge_keys(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'missing_access_review', 'occurred_at' => now()]);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'access_review_missing', 'occurred_at' => now()]);
        Signal::create(['source_id' => $source->id, 'type' => 'risk.detected', 'challenge_key' => 'other_unique', 'occurred_at' => now()]);

        $this->get(route('intelligence.allocore'))
            ->assertOk()
            ->assertSee('missing_access_review')
            ->assertSee('access_review_missing')
            ->assertSee('Possible duplicate challenges');
    }
}
