<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanySourceFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_companies_index_filters_by_source(): void
    {
        $a = Source::create(['key' => 'a', 'name' => 'Alpha', 'type' => 'saas_platform']);
        $b = Source::create(['key' => 'b', 'name' => 'Beta', 'type' => 'saas_platform']);
        Company::create(['source_id' => $a->id, 'external_id' => 'x', 'name' => 'CoAlpha']);
        Company::create(['source_id' => $b->id, 'external_id' => 'y', 'name' => 'CoBeta']);

        $this->get(route('companies.index'))->assertOk()
            ->assertSee('CoAlpha')->assertSee('CoBeta');

        $this->get(route('companies.index', ['source_id' => $a->id]))->assertOk()
            ->assertSee('CoAlpha')->assertDontSee('CoBeta');
    }
}
