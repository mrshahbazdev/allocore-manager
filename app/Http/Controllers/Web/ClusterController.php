<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Pattern;
use App\Models\Signal;
use App\Services\CompanySimilarity;

class ClusterController extends Controller
{
    public function index(CompanySimilarity $similarity)
    {
        $clusters = Company::all()
            ->groupBy(fn (Company $c) => $similarity->cohortFor($c))
            ->map(fn ($group, $cohort) => [
                'cohort' => $cohort,
                'companies' => $group,
                'topChallenges' => Signal::whereIn('company_id', $group->pluck('id'))
                    ->whereNotNull('challenge_key')
                    ->selectRaw('challenge_key, count(*) as total')
                    ->groupBy('challenge_key')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get(),
                // What actually works in this cohort — the cluster's
                // strongest measured evidence.
                'bestMeasures' => Pattern::where('cohort', $cohort)
                    ->with('actionMeasure')
                    ->get()
                    ->filter(fn ($p) => $p->evidenceAdjustedRate() !== null)
                    ->sortByDesc(fn ($p) => $p->evidenceAdjustedRate())
                    ->take(3)
                    ->values(),
            ])
            ->sortByDesc(fn ($c) => $c['companies']->count())
            ->values();

        return view('clusters.index', ['clusters' => $clusters]);
    }
}
