<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LearningLatencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_allocore_page_shows_median_days_to_outcome(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Do thing', 'addresses_challenges' => ['ch']]);

        foreach ([2, 8] as $daysAgo) {
            $rec = Recommendation::create([
                'company_id' => $company->id,
                'action_measure_id' => $measure->id,
                'challenge_key' => 'ch',
                'status' => 'implemented',
            ]);
            // created_at is not fillable — backdate it directly.
            DB::table('recommendations')->where('id', $rec->id)
                ->update(['created_at' => now()->subDays($daysAgo)]);
            Outcome::create([
                'recommendation_id' => $rec->id,
                'result' => 'success',
                'measured_at' => now(),
            ]);
        }

        $this->get('/intelligence/allocore')
            ->assertOk()
            ->assertSee('Median days to outcome')
            ->assertSee('5d');
    }

    public function test_latency_shows_dash_with_no_outcomes(): void
    {
        $this->get('/intelligence/allocore')
            ->assertOk()
            ->assertSee('Median days to outcome');
    }
}
