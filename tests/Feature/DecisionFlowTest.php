<?php

namespace Tests\Feature;

use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\User;
use App\Support\DecisionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecisionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_engine_creates_recommendation_once(): void
    {
        Signal::create(['type' => 'invoice.overdue', 'company_key' => 'acme', 'occurred_at' => now(), 'payload' => []]);

        $engine = new DecisionEngine;
        $this->assertSame(1, $engine->run());
        $this->assertSame(0, $engine->run()); // no duplicate while open

        $this->assertDatabaseHas('recommendations', ['code' => 'invoice.overdue', 'company_key' => 'acme', 'status' => 'open']);
    }

    public function test_outcome_feeds_evidence(): void
    {
        Signal::create(['type' => 'complaint', 'company_key' => 'acme', 'occurred_at' => now(), 'payload' => []]);
        (new DecisionEngine)->run();
        $rec = Recommendation::first();
        Outcome::create(['recommendation_id' => $rec->id, 'result' => 'success']);

        $stats = DecisionEngine::effectiveness('complaint');
        $this->assertSame(1, $stats['tried']);
        $this->assertSame(100, $stats['success_rate']);
    }

    public function test_member_sees_only_own_company_recommendations(): void
    {
        Recommendation::create(['company_key' => 'acme', 'code' => 'x', 'title' => 'T', 'description' => 'D', 'status' => 'open']);
        Recommendation::create(['company_key' => 'other', 'code' => 'x', 'title' => 'T2', 'description' => 'D', 'status' => 'open']);
        $user = User::factory()->create(['role' => 'member', 'company_key' => 'acme']);

        $this->actingAs($user)->get('/app')->assertOk()->assertSee('T')->assertDontSee('T2');
    }

    public function test_outcome_endpoint_marks_done(): void
    {
        $rec = Recommendation::create(['company_key' => 'acme', 'code' => 'x', 'title' => 'T', 'description' => 'D', 'status' => 'open']);
        $user = User::factory()->create(['role' => 'member', 'company_key' => 'acme']);

        $this->actingAs($user)->post("/recommendations/{$rec->id}/outcome", ['result' => 'success'])
            ->assertRedirect();

        $this->assertSame('done', $rec->fresh()->status);
        $this->assertDatabaseHas('outcomes', ['recommendation_id' => $rec->id, 'result' => 'success']);
    }
}
