<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use App\Services\TrendDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrendController extends Controller
{
    public function index(Request $request, TrendDetector $detector)
    {
        $window = (int) $request->query('days', 30);
        $window = in_array($window, [7, 14, 30, 60, 90]) ? $window : 30;

        return view('trends.index', [
            'trends' => $detector->detect($window),
            'goneQuiet' => $detector->goneQuiet(),
            'window' => $window,
            // Risks nobody has ever signalled before — the earliest possible
            // warning the system can give.
            'emerging' => Signal::whereNotNull('challenge_key')
                ->where('occurred_at', '>=', now()->subDays(7))
                ->select('challenge_key', DB::raw('count(*) as recent'), DB::raw('min(occurred_at) as first_seen'))
                ->groupBy('challenge_key')
                ->get()
                ->filter(fn ($row) => ! Signal::where('challenge_key', $row->challenge_key)
                    ->where('occurred_at', '<', now()->subDays(7))->exists())
                ->sortByDesc('recent')
                ->values(),
        ]);
    }
}
