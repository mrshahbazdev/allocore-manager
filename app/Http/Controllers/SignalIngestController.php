<?php

namespace App\Http\Controllers;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalIngestController extends Controller
{
    /**
     * POST /api/v1/signals/{token}
     * Body: {type, occurred_at?, company_key?, user_email?, ...payload fields}
     * or a batch: {signals: [...]}
     */
    public function store(Request $request, string $token): JsonResponse
    {
        $source = Source::where('token', $token)->firstOrFail();

        $items = $request->input('signals');
        if (! is_array($items)) {
            $items = [$request->all()];
        }

        $created = 0;
        foreach ($items as $item) {
            if (! is_array($item) || empty($item['type'])) {
                continue;
            }
            Signal::create([
                'source_id' => $source->id,
                'type' => $item['type'],
                'company_key' => $item['company_key'] ?? null,
                'user_email' => $item['user_email'] ?? null,
                'occurred_at' => $item['occurred_at'] ?? now(),
                'payload' => $item,
            ]);
            $created++;
        }

        return response()->json(['ingested' => $created], 201);
    }
}
