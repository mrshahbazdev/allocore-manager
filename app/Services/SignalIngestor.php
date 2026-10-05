<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Support\Arr;

class SignalIngestor
{
    /**
     * Store a signal from a source platform. Upserts the company stub so
     * company context accumulates as data flows in.
     */
    public function ingest(Source $source, array $data): Signal
    {
        $company = null;

        if ($externalCompanyId = Arr::get($data, 'company.external_id')) {
            $company = Company::updateOrCreate(
                ['source_id' => $source->id, 'external_id' => (string) $externalCompanyId],
                array_filter([
                    'name' => Arr::get($data, 'company.name'),
                    'industry' => Arr::get($data, 'company.industry'),
                    'size' => Arr::get($data, 'company.size'),
                    'maturity' => Arr::get($data, 'company.maturity'),
                    'situation' => Arr::get($data, 'company.situation'),
                ], fn ($v) => $v !== null)
            );
        }

        return Signal::create([
            'source_id' => $source->id,
            'company_id' => $company?->id,
            'external_user_id' => Arr::get($data, 'user_id'),
            'type' => $data['type'],
            'challenge_key' => $data['challenge_key'] ?? null,
            'payload' => $data['payload'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]);
    }
}
