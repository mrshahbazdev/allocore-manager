<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActionMeasure;
use App\Models\Outcome;
use Illuminate\Http\Request;

class ActionMeasureController extends Controller
{
    public function index()
    {
        return view('measures.index', [
            'measures' => ActionMeasure::withCount('recommendations')->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('measures.create');
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

    public function update(Request $request, ActionMeasure $measure)
    {
        $measure->update(['is_active' => $request->boolean('is_active')]);

        return back()->with('status', $measure->is_active ? 'Measure activated.' : 'Measure deactivated.');
    }
}
