<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutcomeFormTest extends TestCase
{
    use RefreshDatabase;

    private function rec(): Recommendation
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'M']);

        return Recommendation::create([
            'company_id' => $company->id, 'action_measure_id' => $measure->id,
            'challenge_key' => 'ch', 'confidence' => 80, 'status' => 'implemented',
        ]);
    }

    public function test_outcome_form_renders(): void
    {
        $this->get(route('recommendations.outcome.edit', $this->rec()))
            ->assertOk()
            ->assertSee('Why did it fail');
    }

    public function test_failure_requires_reason(): void
    {
        $this->post(route('recommendations.outcome', $this->rec()), ['result' => 'failure'])
            ->assertSessionHasErrors('failure_reason');
    }
}
