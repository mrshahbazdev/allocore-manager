<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Services\LearningLoop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutcomeController extends Controller
{
    public function store(Request $request, Recommendation $recommendation, LearningLoop $loop): JsonResponse
    {
        $data = $request->validate([
            'result' => ['required', 'in:success,failure,partial'],
            'failure_reason' => ['nullable', 'string', 'max:255'],
            'metrics' => ['nullable', 'array'],
        ]);

        $outcome = $loop->recordOutcome(
            $recommendation,
            $data['result'],
            $data['failure_reason'] ?? null,
            $data['metrics'] ?? null
        );

        return response()->json($outcome, 201);
    }
}
