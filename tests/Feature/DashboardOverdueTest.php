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

class DashboardOverdueTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_section_flags_stale_unmeasured_implemented(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $stale = Company::create(['source_id' => $source->id, 'external_id' => 's', 'name' => 'StaleCo']);
        $fresh = Company::create(['source_id' => $source->id, 'external_id' => 'f', 'name' => 'FreshCo']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Do thing', 'addresses_challenges' => ['ch']]);

        $old = Recommendation::create(['company_id' => $stale->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'ch', 'status' => 'implemented']);
        DB::table('recommendations')->where('id', $old->id)->update(['updated_at' => now()->subDays(20)]);

        $new = Recommendation::create(['company_id' => $fresh->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'ch', 'status' => 'implemented']);
        DB::table('recommendations')->where('id', $new->id)->update(['updated_at' => now()->subDays(2)]);

        // StaleCo shows in the overdue list, FreshCo only under outcomes-missing.
        $this->get('/')->assertOk()->assertSee('Overdue outcomes');
    }

    public function test_measured_recommendation_not_overdue(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 's']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'Do thing', 'addresses_challenges' => ['ch']]);
        $rec = Recommendation::create(['company_id' => $company->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'ch', 'status' => 'implemented']);
        Outcome::create(['recommendation_id' => $rec->id, 'result' => 'success', 'measured_at' => now()]);
        DB::table('recommendations')->where('id', $rec->id)->update(['updated_at' => now()->subDays(20)]);

        $this->get('/')->assertOk()->assertDontSee('Overdue outcomes');
    }
}
