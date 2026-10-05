<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CompanySimilarity;
use App\Services\RecommendationEngine;

class CompanyController extends Controller
{
    public function index()
    {
        return view('companies.index', [
            'companies' => Company::with('source')->withCount('signals', 'recommendations')->paginate(25),
        ]);
    }

    public function show(Company $company, CompanySimilarity $similarity)
    {
        $similar = $similarity->similarTo($company)->take(10)->map(fn (Company $c) => [
            'company' => $c,
            'score' => $similarity->score($company, $c),
        ]);

        return view('companies.show', [
            'company' => $company->load('source'),
            'recommendations' => $company->recommendations()->with('actionMeasure', 'outcome')->latest()->get(),
            'similar' => $similar,
            'signals' => $company->signals()->latest('occurred_at')->limit(20)->get(),
        ]);
    }

    public function refresh(Company $company, RecommendationEngine $engine)
    {
        $count = $engine->refreshFor($company)->count();

        return redirect()->route('companies.show', $company)
            ->with('status', "{$count} new recommendation(s) generated.");
    }
}
