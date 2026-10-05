<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActionMeasure;
use App\Models\Company;
use App\Models\Outcome;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use App\Models\Source;
use App\Services\TrendDetector;

class DashboardController extends Controller
{
    public function index(TrendDetector $detector)
    {
        return view('dashboard', [
            'sources' => Source::withCount('companies', 'signals')->get(),
            'measures' => ActionMeasure::where('is_active', true)->get(),
            'stats' => [
                'companies' => Company::count(),
                'signals' => Signal::count(),
                'recommendations' => Recommendation::count(),
                'outcomes' => Outcome::count(),
            ],
            'pendingRecs' => Recommendation::with('company', 'actionMeasure')->where('status', 'pending')->latest()->limit(10)->get(),
            'unmeasured' => Recommendation::with('company', 'actionMeasure')->where('status', 'implemented')->whereDoesntHave('outcome')->limit(10)->get(),
            // Feeds that have gone quiet — a dead source starves the loop.
            'staleSources' => Source::where('is_active', true)->whereDoesntHave('signals', fn ($q) => $q->where('occurred_at', '>=', now()->subDays(30)))->get(),
            'recentSignals' => Signal::with('source', 'company')->latest('occurred_at')->limit(15)->get(),
            'topPatterns' => Pattern::with('actionMeasure')->orderByDesc('attempts')->limit(10)->get(),
            'emergingTrends' => $detector->emerging(30)->take(5),
            // Activity pulse: signals per week for the last 8 weeks.
            'weeklySignals' => Signal::where('occurred_at', '>=', now()->subWeeks(8))
                ->get()
                ->groupBy(fn ($s) => $s->occurred_at->startOfWeek()->toDateString())
                ->map->count()
                ->sortKeys(),
        ]);
    }
}
