<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignalExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_returns_jsonl_that_imports_back(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create([
            'source_id' => $source->id, 'external_id' => 'acme', 'name' => 'Acme', 'maturity' => 'growing',
        ]);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'ch', 'occurred_at' => now(),
        ]);

        $response = $this->get(route('sources.export', $source))->assertOk();
        $lines = array_filter(explode("\n", $response->getContent()));
        $this->assertCount(1, $lines);

        $row = json_decode($lines[0], true);
        $this->assertEquals('risk.detected', $row['type']);
        $this->assertEquals('ch', $row['challenge_key']);
        $this->assertEquals('acme', $row['company']['external_id']);
    }
}
