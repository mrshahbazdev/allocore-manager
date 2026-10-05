<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_edit_updates_situation_tags(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);

        $this->get(route('companies.edit', $company))->assertOk();

        $this->put(route('companies.update', $company), [
            'name' => 'Acme GmbH',
            'industry' => 'logistics',
            'maturity' => 'growing',
            'situation' => 'no_it_team, compliance_backlog ,  paper_processes',
        ])->assertRedirect(route('companies.show', $company));

        $company->refresh();
        $this->assertEquals('Acme GmbH', $company->name);
        $this->assertEquals('growing', $company->maturity);
        $this->assertEquals(['no_it_team', 'compliance_backlog', 'paper_processes'], $company->situation);
    }
}
