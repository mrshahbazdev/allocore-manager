<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_page_shows_breakdowns(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'Allocore', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'acme', 'name' => 'Acme']);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'ch1', 'occurred_at' => now(),
        ]);

        $response = $this->get(route('sources.show', $source))->assertOk();
        $response->assertSee('Allocore')
            ->assertSee('risk.detected')
            ->assertSee('ch1')
            ->assertSee('Acme');
    }
}
