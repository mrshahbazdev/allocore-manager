<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyQuietTest extends TestCase
{
    use RefreshDatabase;

    public function test_companies_index_flags_quiet_companies(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $quiet = Company::create(['source_id' => $source->id, 'external_id' => 'q', 'name' => 'QuietCo']);
        $loud = Company::create(['source_id' => $source->id, 'external_id' => 'l', 'name' => 'LoudCo']);
        Signal::create(['source_id' => $source->id, 'company_id' => $quiet->id, 'type' => 't', 'occurred_at' => now()->subDays(60)]);
        Signal::create(['source_id' => $source->id, 'company_id' => $loud->id, 'type' => 't', 'occurred_at' => now()]);

        $html = $this->get(route('companies.index'))->assertOk()->getContent();

        $quietRow = preg_match('/QuietCo.*<\/tr>/s', $html, $m) ? $m[0] : '';
        $loudRow = preg_match('/LoudCo.*<\/tr>/s', $html, $m2) ? $m2[0] : '';
        $this->assertStringContainsString('quiet', $quietRow);
        $this->assertStringNotContainsString('quiet', $loudRow);
    }
}
