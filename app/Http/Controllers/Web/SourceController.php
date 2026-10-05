<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index()
    {
        return view('sources.index', [
            'sources' => Source::withCount('companies', 'signals')
                ->withMax('signals', 'occurred_at')
                ->latest()
                ->get(),
        ]);
    }

    public function create()
    {
        return view('sources.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100', 'unique:sources,key', 'regex:/^[a-z0-9-]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        Source::create($data + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('sources.index')->with('status', 'Source registered.');
    }

    public function show(Source $source)
    {
        $signals = $source->signals()->with('company')->latest('occurred_at')->get();

        // Signals per week for the last 12 weeks.
        $weekly = $signals->where('occurred_at', '>=', now()->subWeeks(12))
            ->groupBy(fn ($s) => $s->occurred_at->startOfWeek()->toDateString())
            ->map->count()
            ->sortKeys();

        $byType = $signals->groupBy('type')->map->count()->sortDesc();
        $byChallenge = $signals->whereNotNull('challenge_key')
            ->groupBy('challenge_key')->map->count()->sortDesc();

        return view('sources.show', [
            'source' => $source,
            'signals' => $signals->take(30),
            'weekly' => $weekly,
            'byType' => $byType,
            'byChallenge' => $byChallenge,
            'companies' => $source->companies()->orderBy('name')->get(),
            'lastSeenAt' => $signals->first()?->occurred_at,
        ]);
    }

    public function edit(Source $source)
    {
        return view('sources.edit', ['source' => $source]);
    }

    public function update(Request $request, Source $source)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $source->update($data + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('sources.index')->with('status', 'Source updated.');
    }
}
