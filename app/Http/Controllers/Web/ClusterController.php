<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CompanySimilarity;

class ClusterController extends Controller
{
    public function index(CompanySimilarity $similarity)
    {
        $clusters = Company::all()
            ->groupBy(fn (Company $c) => $similarity->cohortFor($c))
            ->map(fn ($group, $cohort) => ['cohort' => $cohort, 'companies' => $group])
            ->sortByDesc(fn ($c) => $c['companies']->count())
            ->values();

        return view('clusters.index', ['clusters' => $clusters]);
    }
}
