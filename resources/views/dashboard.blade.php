@extends('layouts.app')
@section('content')
<h1>Ecosystem Overview</h1>

<div class="stat-grid">
    <div class="stat"><div class="num">{{ $stats['companies'] }}</div><div class="lbl">Companies</div></div>
    <div class="stat"><div class="num">{{ $stats['signals'] }}</div><div class="lbl">Signals</div></div>
    <div class="stat"><div class="num">{{ $stats['recommendations'] }}</div><div class="lbl">Recommendations</div></div>
    <div class="stat"><div class="num">{{ $stats['outcomes'] }}</div><div class="lbl">Outcomes measured</div></div>
</div>

@if ($weeklySignals->isNotEmpty())
<div class="card">
    <h2>Activity pulse (last 8 weeks)</h2>
    <table>
        <tr>@foreach ($weeklySignals as $week => $count)<th>{{ \Illuminate\Support\Carbon::parse($week)->format('M d') }}</th>@endforeach</tr>
        <tr>@foreach ($weeklySignals as $count)<td>{{ $count }}</td>@endforeach</tr>
    </table>
    <p class="muted" style="margin-top:.4rem">Signals per week across the whole ecosystem.</p>
</div>
@endif

<div class="card">
    <h2>Needs attention</h2>
    @if ($pendingRecs->isEmpty() && $unmeasured->isEmpty() && $overdue->isEmpty() && $staleSources->isEmpty())
        <p class="muted">Nothing waiting — all recommendations decided and all implemented measures measured.</p>
    @endif
    @if ($staleSources->isNotEmpty())
    <h3 style="margin:.75rem 0 .25rem">Quiet feeds ({{ $staleSources->count() }})</h3>
    <p class="muted">No signal in 30+ days: {{ $staleSources->pluck('name')->implode(', ') }} — a dead feed starves the learning loop.</p>
    @endif
    @if ($pendingRecs->isNotEmpty())
    <h3 style="margin:.75rem 0 .25rem">Decisions waiting ({{ $pendingRecs->count() }})</h3>
    <table>
        <tr><th>Company</th><th>Recommended action</th><th>Challenge</th><th>Confidence</th><th></th></tr>
        @foreach ($pendingRecs as $r)
        <tr>
            <td><a href="{{ route('companies.show', $r->company) }}">{{ $r->company->name ?? $r->company->external_id }}</a></td>
            <td>{{ $r->actionMeasure?->name }}</td>
            <td>{{ $r->challenge_key }}</td>
            <td class="conf">{{ $r->confidence !== null ? number_format($r->confidence, 0).'%' : '—' }}</td>
            <td><a href="{{ route('companies.show', $r->company) }}">Decide</a></td>
        </tr>
        @endforeach
    </table>
    @endif
    @if ($unmeasured->isNotEmpty())
    <h3 style="margin:.75rem 0 .25rem">Outcomes missing ({{ $unmeasured->count() }})</h3>
    <table>
        <tr><th>Company</th><th>Implemented measure</th><th>Challenge</th><th></th></tr>
        @foreach ($unmeasured as $r)
        <tr>
            <td><a href="{{ route('companies.show', $r->company) }}">{{ $r->company->name ?? $r->company->external_id }}</a></td>
            <td>{{ $r->actionMeasure?->name }}</td>
            <td>{{ $r->challenge_key }}</td>
            <td><a href="{{ route('companies.show', $r->company) }}">Measure</a></td>
        </tr>
        @endforeach
    </table>
    @endif
    @if ($overdue->isNotEmpty())
    <h3 style="margin:.75rem 0 .25rem">Overdue outcomes — stalled >14d ({{ $overdue->count() }})</h3>
    <table>
        <tr><th>Company</th><th>Challenge</th><th>Implemented</th><th></th></tr>
        @foreach ($overdue as $r)
        <tr>
            <td><a href="{{ route('companies.show', $r->company) }}">{{ $r->company->name ?? $r->company->external_id }}</a></td>
            <td>{{ $r->challenge_key }}</td>
            <td class="muted">{{ $r->updated_at->diffForHumans() }}</td>
            <td><a href="{{ route('recommendations.outcome.edit', $r) }}">Measure</a></td>
        </tr>
        @endforeach
    </table>
    @endif
</div>

@if ($emergingTrends->isNotEmpty())
<div class="card">
    <h2>Emerging trends <span class="muted">(last 30 days)</span></h2>
    <table>
        <tr><th>Challenge</th><th>Signals</th><th>vs prior 30d</th></tr>
        @foreach ($emergingTrends as $t)
        <tr>
            <td><a href="{{ route('challenges.show', $t['challenge_key']) }}">{{ $t['challenge_key'] }}</a></td>
            <td>{{ $t['recent'] }}</td>
            <td class="conf">{{ $t['growth_pct'] === null ? 'new' : '+'.$t['growth_pct'].'%' }}</td>
        </tr>
        @endforeach
    </table>
    <p class="muted" style="margin-top:.5rem"><a href="{{ route('trends.index') }}">All trends →</a></p>
</div>
@endif
<div class="card">
    <h2>Data providers</h2>
    <table>
        <tr><th>Platform</th><th>Type</th><th>Companies</th><th>Signals</th><th></th></tr>
        @foreach ($sources as $s)
        <tr>
            <td>{{ $s->name }} <span class="muted">({{ $s->key }})</span></td>
            <td>{{ $s->type }}</td>
            <td>{{ $s->companies_count }}</td>
            <td>{{ $s->signals_count }}</td>
            <td><a href="{{ route('intelligence.platform', $s) }}">Product intelligence</a></td>
        </tr>
        @endforeach
        @if ($sources->isEmpty())<tr><td colspan="5" class="muted">No sources registered yet.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Learned patterns</h2>
    <table>
        <tr><th>Challenge</th><th>Action</th><th>Cohort</th><th>Attempts</th><th>Success rate</th></tr>
        @foreach ($topPatterns as $p)
        <tr>
            <td><a href="{{ route('challenges.show', $p->challenge_key) }}">{{ $p->challenge_key }}</a></td>
            <td>{{ $p->actionMeasure?->name }}</td>
            <td>{{ $p->cohort }}</td>
            <td>{{ $p->attempts }}</td>
            <td class="conf">{{ $p->successRate() !== null ? $p->successRate().'%' : '—' }}</td>
        </tr>
        @endforeach
        @if ($topPatterns->isEmpty())<tr><td colspan="5" class="muted">No patterns learned yet — they accumulate as outcomes are measured.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Recent signals</h2>
    <table>
        <tr><th>When</th><th>Source</th><th>Company</th><th>Type</th><th>Challenge</th></tr>
        @foreach ($recentSignals as $sig)
        <tr>
            <td class="muted">{{ $sig->occurred_at->diffForHumans() }}</td>
            <td>{{ $sig->source?->name }}</td>
            <td>{{ $sig->company?->name ?? '—' }}</td>
            <td>{{ $sig->type }}</td>
            <td>{{ $sig->challenge_key ?? '—' }}</td>
        </tr>
        @endforeach
        @if ($recentSignals->isEmpty())<tr><td colspan="5" class="muted">No signals yet. <a href="{{ route('signals.create') }}">Record one</a>.</td></tr>@endif
    </table>
</div>
@endsection
