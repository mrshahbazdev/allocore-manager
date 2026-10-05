<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
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
            ])
            ->sortByDesc(fn ($c) => $c['companies']->count())
            ->values();

        return view('clusters.index', ['clusters' => $clusters]);
    }
}
