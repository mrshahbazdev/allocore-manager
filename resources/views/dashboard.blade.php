@extends('layouts.app')
@section('content')
<h1>Ecosystem Overview</h1>

<div class="stat-grid">
    <div class="stat"><div class="num">{{ $stats['companies'] }}</div><div class="lbl">Companies</div></div>
    <div class="stat"><div class="num">{{ $stats['signals'] }}</div><div class="lbl">Signals</div></div>
    <div class="stat"><div class="num">{{ $stats['recommendations'] }}</div><div class="lbl">Recommendations</div></div>
    <div class="stat"><div class="num">{{ $stats['outcomes'] }}</div><div class="lbl">Outcomes measured</div></div>
</div>

@if ($emergingTrends->isNotEmpty())
<div class="card">
    <h2>Emerging trends <span class="muted">(last 30 days)</span></h2>
    <table>
        <tr><th>Challenge</th><th>Signals</th><th>vs prior 30d</th></tr>
        @foreach ($emergingTrends as $t)
        <tr>
            <td>{{ $t['challenge_key'] }}</td>
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
            <td>{{ $p->challenge_key }}</td>
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
