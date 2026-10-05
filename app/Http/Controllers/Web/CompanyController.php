<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Signal;
use App\Services\CompanySimilarity;
use App\Services\RecommendationEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        return view('companies.index', [
            'companies' => Company::with('source')
                ->withCount('signals', 'recommendations')
                ->withCount(['recommendations as pending_recs_count' => fn ($q) => $q->where('status', 'pending')])
                ->withCount(['recommendations as unmeasured_count' => fn ($q) => $q->where('status', 'implemented')->whereDoesntHave('outcome')])
                ->withMax('signals', 'occurred_at')
                ->orderByDesc('pending_recs_count')
                ->orderByDesc('unmeasured_count')
                ->paginate(25),
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

    public function timeline(Company $company)
    {
        $events = collect();

        foreach ($company->signals()->with('source')->latest('occurred_at')->limit(100)->get() as $s) {
            $events->push([
                'at' => $s->occurred_at,
                'kind' => 'signal',
                'label' => $s->type,
                'detail' => collect([$s->challenge_key, $s->source?->name])->filter()->implode(' · '),
            ]);
        }

        foreach ($company->recommendations()->with('actionMeasure', 'outcome')->latest()->limit(100)->get() as $r) {
            $events->push([
                'at' => $r->created_at,
                'kind' => 'recommendation',
                'label' => $r->actionMeasure?->name ?? 'recommendation',
                'detail' => "{$r->status} · confidence {$r->confidence}%",
            ]);
            if ($r->outcome) {
                $events->push([
                    'at' => $r->outcome->measured_at,
                    'kind' => 'outcome',
                    'label' => $r->actionMeasure?->name ?? 'outcome',
                    'detail' => $r->outcome->result.($r->outcome->failure_reason ? " · {$r->outcome->failure_reason}" : ''),
                ]);
            }
        }

        return view('companies.timeline', [
            'company' => $company,
            'events' => $events->sortByDesc('at')->values(),
        ]);
    }
}
