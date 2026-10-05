<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Signal;
use App\Models\Source;
use App\Services\CompanySimilarity;
use App\Services\RecommendationEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $sourceId = $request->query('source_id', '');

        return view('companies.index', [
            'companies' => Company::with('source')
                ->withCount('signals', 'recommendations')
                ->withCount(['recommendations as pending_recs_count' => fn ($q) => $q->where('status', 'pending')])
                ->withCount(['recommendations as unmeasured_count' => fn ($q) => $q->where('status', 'implemented')->whereDoesntHave('outcome')])
                ->withMax('signals', 'occurred_at')
                ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('external_id', 'like', "%{$q}%")
                        ->orWhere('industry', 'like', "%{$q}%");
                }))
                ->when($sourceId !== '', fn ($query) => $query->where('source_id', $sourceId))
                ->orderByDesc('pending_recs_count')
                ->orderByDesc('unmeasured_count')
                ->paginate(25)
                ->withQueryString(),
            'q' => $q,
            'sourceId' => $sourceId,
            'sources' => Source::orderBy('name')->get(),
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

    public function edit(Company $company)
    {
        return view('companies.edit', ['company' => $company]);
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'maturity' => ['nullable', 'string', 'max:255'],
            'situation' => ['nullable', 'string'],
        ]);

        // Situation is stored as an array of tags; the form edits it as
        // comma-separated text.
        $data['situation'] = collect(explode(',', $data['situation'] ?? ''))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        $company->update($data);

        return redirect()->route('companies.show', $company)->with('status', 'Company updated.');
    }

    public function refresh(Company $company, RecommendationEngine $engine)
    {
        $count = $engine->refreshFor($company)->count();

        return redirect()->route('companies.show', $company)
            ->with('status', "{$count} new recommendation(s) generated.");
    }
}
