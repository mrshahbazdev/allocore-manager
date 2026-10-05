<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkImplementedTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepted_recommendation_can_be_marked_implemented(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);
        $rec = Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'status' => 'accepted', 'confidence' => 70,
        ]);

        $this->get(route('companies.show', $company))->assertSee('Mark implemented');

        $this->patch(route('recommendations.update', $rec), ['status' => 'implemented']);
        $this->assertSame('implemented', $rec->fresh()->status);
    }
}
