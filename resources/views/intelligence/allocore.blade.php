@extends('layouts.app')
@section('content')
<h1>Allocore team — learning system</h1>
<div class="stat-grid">
    <div class="stat"><div class="num">{{ $companies }}</div><div class="lbl">Companies</div></div>
    <div class="stat"><div class="num">{{ $signals }}</div><div class="lbl">Signals</div></div>
    <div class="stat"><div class="num">{{ $recommendations }}</div><div class="lbl">Recommendations</div></div>
    <div class="stat"><div class="num">{{ $outcomes }}</div><div class="lbl">Outcomes</div></div>
    <div class="stat"><div class="num">{{ $overallSuccessRate !== null ? $overallSuccessRate.'%' : '—' }}</div><div class="lbl">Success rate</div></div>
</div>
<div class="card">
    <h2>Patterns</h2>
    <table>
        <tr><th>Challenge</th><th>Action</th><th>Cohort</th><th>Attempts</th><th>Success</th><th>Failures</th><th>Top failure reasons</th></tr>
        @foreach ($patterns as $p)
        <tr>
            <td>{{ $p->challenge_key }}</td>
            <td>{{ $p->actionMeasure?->name }}</td>
            <td>{{ $p->cohort }}</td>
            <td>{{ $p->attempts }}</td>
            <td class="conf">{{ $p->successRate() !== null ? $p->successRate().'%' : '—' }}</td>
            <td>{{ $p->failures }}</td>
            <td class="muted">{{ collect($p->failure_reasons ?? [])->sortDesc()->take(3)->map(fn ($c, $r) => "$r ($c)")->implode(', ') }}</td>
        </tr>
        @endforeach
        @if ($patterns->isEmpty())<tr><td colspan="7" class="muted">No patterns yet.</td></tr>@endif
    </table>
</div>
@endsection
