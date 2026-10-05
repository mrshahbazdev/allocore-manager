<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DisavoGrowthTest extends TestCase
{
    use RefreshDatabase;

    public function test_disavo_page_shows_monthly_growth_rows(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform', 'ingest_token' => 't', 'is_active' => true]);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c1', 'name' => 'Co']);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'x', 'challenge_key' => 'k', 'payload' => [],
            'occurred_at' => now()->subMonths(2),
        ]);
        DB::table('companies')->update(['created_at' => now()->subMonths(2)]);
        $month = now()->subMonths(2)->format('Y-m');

        $this->get(route('intelligence.disavo'))->assertOk()->assertSee($month);
    }
}
