<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RecommendationEngine;
use App\Services\SignalIngestor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalController extends Controller
{
    public function store(Request $request, SignalIngestor $ingestor, RecommendationEngine $engine): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'challenge_key' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'string', 'max:100'],
            'payload' => ['nullable', 'array'],
            'occurred_at' => ['nullable', 'date'],
            'company.external_id' => ['nullable', 'string', 'max:100'],
            'company.name' => ['nullable', 'string', 'max:255'],
            'company.industry' => ['nullable', 'string', 'max:100'],
            'company.size' => ['nullable', 'integer', 'min:0'],
            'company.maturity' => ['nullable', 'string', 'max:50'],
            'company.situation' => ['nullable', 'array'],
        ]);

        $signal = $ingestor->ingest($request->attributes->get('source'), $data);

        // A challenge signal triggers the advisor loop immediately.
        $recommendations = $signal->company_id && $signal->challenge_key
            ? $engine->recommendFor($signal->company, $signal->challenge_key, $signal)
            : collect();

        return response()->json([
            'signal_id' => $signal->id,
            'recommendations' => $recommendations,
        ], 201);
    }
}
