<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActionMeasure;
use App\Models\Pattern;
use App\Models\Recommendation;
use App\Models\Signal;
use Illuminate\Support\Facades\DB;

class ChallengeController extends Controller
{
    public function index()
    {
        return view('challenges.index', [
            'challenges' => Signal::whereNotNull('challenge_key')
                ->select('challenge_key', DB::raw('count(*) as signals'))
                ->selectRaw('count(distinct company_id) as companies')
                ->selectRaw('max(occurred_at) as last_seen')
                ->groupBy('challenge_key')
                ->orderByDesc('signals')
                ->get(),
            'covered' => ActionMeasure::where('is_active', true)
                ->get()->flatMap->addresses_challenges->unique(),
        ]);
    }

    public function show(string $challenge)
    {
        $signals = Signal::where('challenge_key', $challenge)
            ->with(['company', 'source'])
            ->latest('occurred_at')
            ->get();

        $bySource = $signals->groupBy('source_id')
            ->map(fn ($rows) => ['name' => $rows->first()->source?->name, 'count' => $rows->count()])
            ->sortByDesc('count');

        $byCompany = $signals->whereNotNull('company_id')
            ->groupBy('company_id')
            ->map(fn ($rows) => ['company' => $rows->first()->company, 'count' => $rows->count()])
            ->sortByDesc('count');

        // Signals per week for the last 12 weeks.
        $weekly = $signals->where('occurred_at', '>=', now()->subWeeks(12))
            ->groupBy(fn ($s) => $s->occurred_at->startOfWeek()->toDateString())
            ->map->count()
            ->sortKeys();

        return view('challenges.show', [
            'challenge' => $challenge,
            'signalCount' => $signals->count(),
            'companyCount' => $signals->pluck('company_id')->filter()->unique()->count(),
            'bySource' => $bySource,
            'byCompany' => $byCompany,
            'weekly' => $weekly,
            'patterns' => Pattern::where('challenge_key', $challenge)
                ->with('actionMeasure')
                ->orderByDesc(DB::raw('successes * 1.0 / max(attempts,1)'))
                ->get(),
            'recommendations' => Recommendation::where('challenge_key', $challenge)
                ->with(['company', 'actionMeasure'])
                ->latest()
                ->limit(20)
                ->get(),
            // The answer to "what works here": the measure with the strongest
            // effective evidence (freshness- and adoption-weighted).
            'bestMeasure' => Pattern::where('challenge_key', $challenge)
                ->where('attempts', '>=', 1)
                ->with('actionMeasure')
                ->get()
                ->filter(fn (Pattern $p) => $p->actionMeasure?->is_active)
                ->sortByDesc(fn (Pattern $p) => $p->effectiveRate() ?? 0)
                ->first(),
            'lastSeenAt' => $signals->first()?->occurred_at,
        ]);
    }
}
