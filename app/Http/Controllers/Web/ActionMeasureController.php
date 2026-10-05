<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActionMeasure;
use App\Models\Outcome;
use App\Models\Signal;
use Illuminate\Http\Request;

class ActionMeasureController extends Controller
{
    public function index()
    {
        return view('measures.index', [
            'measures' => ActionMeasure::withCount('recommendations')->latest()->get(),
            // Challenges that have actually been signalled — a measure whose
            // entire address list is unobserved is catalog dead weight.
            'signalled' => Signal::whereNotNull('challenge_key')->distinct()->pluck('challenge_key'),
        ]);
    }

    public function create(Request $request)
    {
        return view('measures.create', [
            'prefillChallenge' => $request->query('challenge', ''),
        ]);
    }

    public function show(ActionMeasure $measure)
    {
        $outcomes = Outcome::whereIn('recommendation_id', $measure->recommendations()->select('id'));

        return view('measures.show', [
            'measure' => $measure,
            'recommendations' => $measure->recommendations()->count(),
            'byStatus' => $measure->recommendations()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'outcomes' => $outcomes->count(),
            'successRate' => $outcomes->count() > 0
                ? round((clone $outcomes)->whereIn('result', ['success', 'partial'])->count() / $outcomes->count() * 100, 1)
                : null,
            'patterns' => $measure->patterns()->orderByDesc('attempts')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:action_measures,key', 'regex:/^[a-z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'addresses_challenges' => ['nullable', 'string', 'max:500'],
        ]);

        ActionMeasure::create([
            'key' => $data['key'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'addresses_challenges' => collect(explode(',', $data['addresses_challenges'] ?? ''))
                ->map(fn ($t) => trim($t))->filter()->values()->all(),
        ]);

        return redirect()->route('measures.index')->with('status', 'Measure added to catalog.');
    }

    public function edit(ActionMeasure $measure)
    {
        return view('measures.edit', ['measure' => $measure]);
    }

    public function update(Request $request, ActionMeasure $measure)
    {
        if ($request->has('is_active') && ! $request->has('name')) {
            $measure->update(['is_active' => $request->boolean('is_active')]);

            return back()->with('status', $measure->is_active ? 'Measure activated.' : 'Measure deactivated.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'addresses_challenges' => ['nullable', 'string', 'max:500'],
        ]);

        $measure->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'addresses_challenges' => collect(explode(',', $data['addresses_challenges'] ?? ''))
                ->map(fn ($t) => trim($t))->filter()->values()->all(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('measures.show', $measure)->with('status', 'Measure updated.');
    }
}
