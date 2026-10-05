<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Source;
use App\Services\RecommendationEngine;
use App\Services\SignalIngestor;
use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function create()
    {
        return view('signals.create', [
            'sources' => Source::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request, SignalIngestor $ingestor, RecommendationEngine $engine)
    {
        $data = $request->validate([
            'source_id' => ['required', 'exists:sources,id'],
            'type' => ['required', 'string', 'max:100'],
            'challenge_key' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'string', 'max:100'],
            'occurred_at' => ['nullable', 'date'],
            'company_external_id' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_industry' => ['nullable', 'string', 'max:100'],
            'company_size' => ['nullable', 'integer', 'min:0'],
            'company_maturity' => ['nullable', 'string', 'max:50'],
            'company_situation' => ['nullable', 'string', 'max:500'],
        ]);

        $situation = collect(explode(',', $data['company_situation'] ?? ''))
            ->map(fn ($t) => trim($t))->filter()->values()->all();

        $signal = $ingestor->ingest(Source::findOrFail($data['source_id']), [
            'type' => $data['type'],
            'challenge_key' => $data['challenge_key'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? null,
            'company' => [
                'external_id' => $data['company_external_id'] ?? null,
                'name' => $data['company_name'] ?? null,
                'industry' => $data['company_industry'] ?? null,
                'size' => $data['company_size'] ?? null,
                'maturity' => $data['company_maturity'] ?? null,
                'situation' => $situation,
            ],
        ]);

        // A challenge signal triggers the advisor loop immediately.
        $count = 0;
        if ($signal->company_id && $signal->challenge_key) {
            $count = $engine->recommendFor($signal->company, $signal->challenge_key, $signal)->count();
        }

        return redirect()->route('dashboard')
            ->with('status', "Signal recorded. {$count} recommendation(s) generated.");
    }
}
