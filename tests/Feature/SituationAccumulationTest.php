<?php

namespace Tests\Feature;

use App\Models\Source;
use App\Services\SignalIngestor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SituationAccumulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_situation_tags_accumulate_across_signals(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $ingestor = app(SignalIngestor::class);

        $ingestor->ingest($source, [
            'type' => 'risk.detected',
            'company' => ['external_id' => 'acme', 'situation' => ['no_it_team', 'compliance_backlog']],
        ]);
        $ingestor->ingest($source, [
            'type' => 'risk.detected',
            'company' => ['external_id' => 'acme', 'situation' => ['paper_processes']],
        ]);
        $ingestor->ingest($source, [
            'type' => 'risk.detected',
            'company' => ['external_id' => 'acme', 'name' => 'Acme GmbH'],
        ]);

        $company = $source->companies()->where('external_id', 'acme')->first();
        $this->assertEquals(['no_it_team', 'compliance_backlog', 'paper_processes'], $company->situation);
        $this->assertEquals('Acme GmbH', $company->name);
    }
}
