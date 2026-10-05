<?php

namespace App\Services;

use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use Illuminate\Support\Arr;

class SignalIngestor
{
    public function __construct(private LearningLoop $loop) {}

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

        $signal = Signal::create([
            'source_id' => $source->id,
            'company_id' => $company?->id,
            'external_user_id' => Arr::get($data, 'user_id'),
            'type' => $data['type'],
            'challenge_key' => $data['challenge_key'] ?? null,
            'payload' => $data['payload'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]);

        $this->applyLifecycleSignal($signal);

        return $signal;
    }

    /**
     * Platforms can drive the loop with lifecycle signals instead of
     * clicking buttons:
     *   - type "action.implemented" + payload.measure_key  → mark the
     *     matching recommendation implemented
     *   - type "outcome.measured"  + payload.measure_key/result
     *     (optionally recommendation_id, failure_reason) → record outcome,
     *     which feeds back into pattern statistics
     */
    private function applyLifecycleSignal(Signal $signal): void
    {
        $payload = $signal->payload ?? [];

        $recommendation = null;
        if ($id = Arr::get($payload, 'recommendation_id')) {
            $recommendation = Recommendation::find($id);
        } elseif ($key = Arr::get($payload, 'measure_key')) {
            $measure = ActionMeasure::where('key', $key)->first();
            if ($measure && $signal->company_id) {
                $recommendation = Recommendation::where('company_id', $signal->company_id)
                    ->where('action_measure_id', $measure->id)
                    ->whereIn('status', [Recommendation::STATUS_PENDING, Recommendation::STATUS_ACCEPTED])
                    ->latest()
                    ->first();
            }
        }

        if (! $recommendation) {
            return;
        }

        match ($signal->type) {
            'action.implemented' => $recommendation->update(['status' => Recommendation::STATUS_IMPLEMENTED]),
            'outcome.measured' => $this->loop->recordOutcome(
                $recommendation,
                Arr::get($payload, 'result', 'success'),
                Arr::get($payload, 'failure_reason'),
                Arr::get($payload, 'metrics')
            ),
            default => null,
        };
    }
}
