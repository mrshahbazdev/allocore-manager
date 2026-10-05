<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_index_search_filters_by_name(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        Company::create(['source_id' => $source->id, 'external_id' => 'a', 'name' => 'Alpha GmbH']);
        Company::create(['source_id' => $source->id, 'external_id' => 'b', 'name' => 'Beta AG']);

        $this->get(route('companies.index', ['q' => 'Alpha']))
            ->assertOk()->assertSee('Alpha GmbH')->assertDontSee('Beta AG');
    }
}
