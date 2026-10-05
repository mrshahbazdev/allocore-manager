@extends('layouts.app')
@section('content')
<h1>{{ $source->name }} <a href="{{ route('sources.edit', $source) }}" class="muted" style="font-size:.8rem">Edit</a></h1>
<p class="muted"><code>{{ $source->key }}</code> · {{ $source->type }} · token <code>{{ $source->ingest_token }}</code></p>

<div class="stats">
    <div class="stat"><div class="num">{{ $signals->count() }}</div><div class="lbl">Signals</div></div>
    <div class="stat"><div class="num">{{ $companies->count() }}</div><div class="lbl">Companies</div></div>
    <div class="stat"><div class="num">{{ $lastSeenAt?->diffForHumans() ?? '—' }}</div><div class="lbl">Last signal</div></div>
</div>

@if ($weekly->isNotEmpty())
<div class="card">
    <h2>Signals per week (12w)</h2>
    <table>
        <tr><th>Week</th><th>Signals</th></tr>
        @foreach ($weekly as $week => $count)
        <tr><td>{{ $week }}</td><td>{{ $count }}</td></tr>
        @endforeach
    </table>
</div>
@endif

<div class="card">
    <h2>Signals by type</h2>
    <table>
        <tr><th>Type</th><th>Count</th></tr>
        @foreach ($byType as $type => $count)
        <tr><td><code>{{ $type }}</code></td><td>{{ $count }}</td></tr>
        @endforeach
        @if ($byType->isEmpty())<tr><td colspan="2" class="muted">No signals yet — <a href="{{ route('sources.import', $source) }}">import an export</a>.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Signals by challenge</h2>
    <table>
        <tr><th>Challenge</th><th>Count</th></tr>
        @foreach ($byChallenge as $challenge => $count)
        <tr><td><a href="{{ route('challenges.show', $challenge) }}"><code>{{ $challenge }}</code></a></td><td>{{ $count }}</td></tr>
        @endforeach
        @if ($byChallenge->isEmpty())<tr><td colspan="2" class="muted">None.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Emerging trends (30d vs prior 30d)</h2>
    <table>
        <tr><th>Challenge</th><th>Recent</th><th>Prior</th><th>Trend</th></tr>
        @foreach ($trends as $t)
        <tr>
            <td><a href="{{ route('challenges.show', $t['challenge_key']) }}"><code>{{ $t['challenge_key'] }}</code></a></td>
            <td>{{ $t['recent'] }}</td>
            <td class="muted">{{ $t['previous'] }}</td>
            <td>
                @if ($t['direction'] === 'rising')
                    <span class="badge b-failure">rising {{ $t['growth_pct'] !== null ? "+{$t['growth_pct']}%" : 'new' }}</span>
                @elseif ($t['direction'] === 'falling')
                    <span class="badge b-partial">falling {{ $t['growth_pct'] }}%</span>
                @else
                    <span class="badge b-pending">stable</span>
                @endif
            </td>
        </tr>
        @endforeach
        @if ($trends->isEmpty())<tr><td colspan="4" class="muted">No challenge signals yet.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Companies reporting</h2>
    <table>
        <tr><th>Company</th><th>External id</th><th>Maturity</th></tr>
        @foreach ($companies as $c)
        <tr>
            <td><a href="{{ route('companies.show', $c) }}">{{ $c->name }}</a></td>
            <td class="muted">{{ $c->external_id }}</td>
            <td>{{ $c->maturity ?? '—' }}</td>
        </tr>
        @endforeach
        @if ($companies->isEmpty())<tr><td colspan="3" class="muted">None yet.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Recent signals</h2>
    <table>
        <tr><th>Type</th><th>Challenge</th><th>Company</th><th>Occurred</th></tr>
        @foreach ($signals as $s)
        <tr>
            <td><code>{{ $s->type }}</code></td>
            <td>{{ $s->challenge_key ?? '—' }}</td>
            <td>{{ $s->company?->name ?? '—' }}</td>
            <td class="muted">{{ $s->occurred_at?->diffForHumans() }}</td>
        </tr>
        @endforeach
        @if ($signals->isEmpty())<tr><td colspan="4" class="muted">None yet.</td></tr>@endif
    </table>
</div>
@endsection
