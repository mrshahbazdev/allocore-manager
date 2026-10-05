<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkDecideTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_update_applies_to_selected_pending_only(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Co']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M', 'addresses_challenges' => ['x'], 'is_active' => true]);
        $mk = fn ($status) => Recommendation::create(['company_id' => $company->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'x', 'status' => $status, 'confidence' => 80]);
        $a = $mk('pending');
        $b = $mk('pending');
        $done = $mk('implemented');

        $this->post(route('recommendations.bulk'), ['ids' => [$a->id, $b->id, $done->id], 'status' => 'dismissed'])
            ->assertRedirect();

        $this->assertEquals('dismissed', $a->fresh()->status);
        $this->assertEquals('dismissed', $b->fresh()->status);
        $this->assertEquals('implemented', $done->fresh()->status); // not pending — untouched
    }
}
