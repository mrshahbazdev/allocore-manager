<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DigestCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_command_prints_counts(): void
    {
        $source = Source::create(['key' => 's', 'name' => 'S', 'type' => 'saas_platform']);
        $company = Company::create(['source_id' => $source->id, 'external_id' => 'c', 'name' => 'Acme']);
        Signal::create([
            'source_id' => $source->id, 'company_id' => $company->id,
            'type' => 'risk.detected', 'challenge_key' => 'ch', 'occurred_at' => now(),
        ]);

        Artisan::call('allocore:digest');
        $output = Artisan::output();

        $this->assertStringContainsString('signals: 1', $output);
        $this->assertStringContainsString('ch (1)', $output);
    }
}
