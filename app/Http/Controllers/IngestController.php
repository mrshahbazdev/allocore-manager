<?php

namespace App\Http\Controllers;

use App\Models\Signal;
use App\Models\Source;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngestController extends Controller
{
    /**
     * Signal ingest — each connected app holds a source token and posts
     * events here. Accepts one signal or a {signals: [...]} batch.
     */
    public function store(Request $request, string $token): JsonResponse
    {
        $source = Source::where('token', $token)->first();
        abort_if(! $source, 401);

        $items = $request->input('signals', [$request->only([
            'type', 'company_key', 'user_email', 'payload', 'occurred_at',
        ])]);

        $created = 0;
        foreach ($items as $item) {
            $valid = validator($item, [
                'type' => 'required|string|max:120',
                'company_key' => 'nullable|string|max:120',
                'user_email' => 'nullable|email|max:255',
                'payload' => 'nullable|array',
                'occurred_at' => 'nullable|date',
            ])->validate();

            Signal::create([
                'source_id' => $source->id,
                'type' => $valid['type'],
                'company_key' => $valid['company_key'] ?? null,
                'user_email' => $valid['user_email'] ?? null,
                'payload' => $valid['payload'] ?? [],
                'occurred_at' => $valid['occurred_at'] ?? now(),
            ]);
            $created++;
        }

        return response()->json(['ok' => true, 'created' => $created]);
    }
}
