<?php

namespace Tests\Feature;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardStalledTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_flags_accepted_recs_stalled_over_7d(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c1', 'name' => 'StallCo']);
        $measure = ActionMeasure::create(['key' => 'm', 'name' => 'StallMeasure', 'addresses_challenges' => ['x'], 'is_active' => true]);
        $rec = Recommendation::create(['company_id' => $company->id, 'action_measure_id' => $measure->id, 'challenge_key' => 'x', 'status' => 'accepted', 'confidence' => 80]);
        DB::table('recommendations')->where('id', $rec->id)->update(['updated_at' => now()->subDays(10)]);

        $this->get(route('dashboard'))->assertOk()
            ->assertSee('Stalled after accept')->assertSee('StallCo');
    }
}
