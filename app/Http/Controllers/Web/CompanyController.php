<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Signal;
use App\Services\CompanySimilarity;
use App\Services\RecommendationEngine;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        return view('companies.index', [
            'companies' => Company::with('source')->withCount('signals', 'recommendations')->paginate(25),
        ]);
    }

    public function show(Company $company, CompanySimilarity $similarity, RecommendationEngine $engine)
    {
        $similar = $similarity->similarTo($company)->take(10)->map(fn (Company $c) => [
            'company' => $c,
            'score' => $similarity->score($company, $c),
        ]);

        // Challenges that often appear alongside this company's challenges
        // across the ecosystem — "companies like yours also face X".
        $ownChallenges = $company->signals()->whereNotNull('challenge_key')->distinct()->pluck('challenge_key');
        $relatedChallenges = collect();
        if ($ownChallenges->isNotEmpty()) {
            $peerIds = Signal::whereIn('challenge_key', $ownChallenges)
                ->where('company_id', '!=', $company->id)
                ->distinct()->pluck('company_id');

            $relatedChallenges = Signal::whereIn('company_id', $peerIds)
                ->whereNotNull('challenge_key')
                ->whereNotIn('challenge_key', $ownChallenges)
                ->select('challenge_key', DB::raw('count(distinct company_id) as companies'))
                ->groupBy('challenge_key')
                ->orderByDesc('companies')
                ->limit(5)
                ->get();
        }

        return view('companies.show', [
            'company' => $company->load('source'),
            'recommendations' => $company->recommendations()->with('actionMeasure', 'outcome')->latest()->get(),
            'similar' => $similar,
            'bestNextAction' => $engine->bestNextAction($company),
            'signals' => $company->signals()->latest('occurred_at')->limit(20)->get(),
            'relatedChallenges' => $relatedChallenges,
        ]);
    }

    public function refresh(Company $company, RecommendationEngine $engine)
    {
        $count = $engine->refreshFor($company)->count();

        return redirect()->route('companies.show', $company)
            ->with('status', "{$count} new recommendation(s) generated.");
    }
}
