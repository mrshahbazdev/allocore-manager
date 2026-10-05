<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Source;
use App\Services\RecommendationEngine;
use App\Services\SignalIngestor;
use Illuminate\Http\Request;

class SignalImportController extends Controller
{
    /**
     * Bulk-import signals from a platform export (one JSON object per line).
     * This is the connector path until platform-specific pullers exist.
     */
    public function create(Source $source)
    {
        return view('sources.import', ['source' => $source]);
    }

    /**
     * Export every signal for a source as JSONL — same format the import
     * accepts, so exports round-trip and platforms can verify ingestion.
     */
    public function export(Source $source)
    {
        $lines = $source->signals()
            ->with('company')
            ->orderBy('occurred_at')
            ->get()
            ->map(function ($signal) {
                return json_encode(array_filter([
                    'type' => $signal->type,
                    'challenge_key' => $signal->challenge_key,
                    'external_user_id' => $signal->external_user_id,
                    'occurred_at' => $signal->occurred_at?->toIso8601String(),
                    'payload' => $signal->payload,
                    'company' => $signal->company ? array_filter([
                        'external_id' => $signal->company->external_id,
                        'name' => $signal->company->name,
                        'industry' => $signal->company->industry,
                        'maturity' => $signal->company->maturity,
                        'situation' => $signal->company->situation,
                    ]) : null,
                ], fn ($v) => $v !== null));
            })
            ->implode("\n");

        return response($lines."\n", 200, [
            'Content-Type' => 'application/jsonl',
            'Content-Disposition' => "attachment; filename=\"{$source->key}-signals.jsonl\"",
        ]);
    }

    public function store(Request $request, Source $source, SignalIngestor $ingestor, RecommendationEngine $engine)
    {
        $data = $request->validate(['lines' => ['required', 'string', 'max:2000000']]);

        $imported = 0;
        $recommendations = 0;
        $errors = [];

        foreach (preg_split('/\r?\n/', trim($data['lines'])) as $i => $line) {
            if (trim($line) === '') {
                continue;
            }

            $row = json_decode($line, true);
            if (! is_array($row) || empty($row['type'])) {
                $errors[] = 'Line '.($i + 1).': invalid JSON or missing "type"';

                continue;
            }

            $signal = $ingestor->ingest($source, $row);
            $imported++;

            if ($signal->company_id && $signal->challenge_key) {
                $recommendations += $engine->recommendFor($signal->company, $signal->challenge_key, $signal)->count();
            }
        }

        return redirect()->route('sources.index')->with('status',
            "{$imported} signal(s) imported, {$recommendations} recommendation(s) generated."
            .($errors ? ' '.implode('; ', array_slice($errors, 0, 3)) : ''));
    }
}
