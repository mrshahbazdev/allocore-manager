<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Outcome;
use App\Models\Recommendation;
use App\Models\Signal;
use Illuminate\Support\Facades\DB;

/**
 * "What changed since yesterday" — the one-screen morning briefing
 * for whoever runs the ecosystem.
 */
class DigestController extends Controller
{
    public function index()
    {
        $since = now()->subDay();

        return view('digest', [
            'since' => $since,
            'signals' => Signal::where('occurred_at', '>=', $since)->count(),
            'topChallenges' => Signal::where('occurred_at', '>=', $since)
                ->whereNotNull('challenge_key')
                ->select('challenge_key', DB::raw('count(*) as total'))
                ->groupBy('challenge_key')->orderByDesc('total')->limit(10)->get(),
            'newRecs' => Recommendation::with('company', 'actionMeasure')
                ->where('created_at', '>=', $since)->latest()->limit(20)->get(),
            'newOutcomes' => Outcome::with('recommendation.company', 'recommendation.actionMeasure')
                ->where('measured_at', '>=', $since)->latest('measured_at')->limit(20)->get(),
            'pendingCount' => Recommendation::where('status', 'pending')->count(),
            'unmeasuredCount' => Recommendation::where('status', 'implemented')->whereDoesntHave('outcome')->count(),
        ]);
    }
}
