<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Services\LearningLoop;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function update(Request $request, Recommendation $recommendation)
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,implemented,dismissed'],
        ]);

        $recommendation->update($data);

        return back()->with('status', "Recommendation {$data['status']}.");
    }

    public function editOutcome(Recommendation $recommendation)
    {
        return view('recommendations.outcome', ['recommendation' => $recommendation]);
    }

    public function outcome(Request $request, Recommendation $recommendation, LearningLoop $loop)
    {
        $data = $request->validate([
            'result' => ['required', 'in:success,failure,partial'],
            'failure_reason' => ['nullable', 'string', 'max:255', 'required_if:result,failure'],
        ]);

        $loop->recordOutcome(
            $recommendation,
            $data['result'],
            $data['failure_reason'] ?? null
        );

        return back()->with('status', 'Outcome recorded — patterns updated.');
    }
}
