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
