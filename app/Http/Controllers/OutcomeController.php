<?php

namespace App\Http\Controllers;

use App\Models\Outcome;
use App\Models\Recommendation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OutcomeController extends Controller
{
    public function store(Request $request, Recommendation $recommendation): RedirectResponse
    {
        $data = $request->validate([
            'result' => 'required|in:success,failed,unknown,dismissed',
            'note' => 'nullable|string|max:2000',
        ]);

        Outcome::create(['recommendation_id' => $recommendation->id] + $data);

        $recommendation->status = $data['result'] === 'dismissed' ? 'dismissed' : 'done';
        $recommendation->save();

        return back()->with('status', 'ui.outcome_recorded');
    }

    public function reopen(Recommendation $recommendation): RedirectResponse
    {
        abort_unless(in_array($recommendation->status, ['done', 'dismissed', 'expired']), 422);

        $recommendation->status = 'open';
        $recommendation->save();

        return back()->with('status', 'ui.reopened');
    }
}
