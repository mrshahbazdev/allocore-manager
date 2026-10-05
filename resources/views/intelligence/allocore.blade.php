@extends('layouts.app')
@section('content')
<h1>Allocore team — learning system</h1>
<div class="stat-grid">
    <div class="stat"><div class="num">{{ $companies }}</div><div class="lbl">Companies</div></div>
    <div class="stat"><div class="num">{{ $signals }}</div><div class="lbl">Signals</div></div>
    <div class="stat"><div class="num">{{ $recommendations }}</div><div class="lbl">Recommendations</div></div>
    <div class="stat"><div class="num">{{ $outcomes }}</div><div class="lbl">Outcomes</div></div>
    <div class="stat"><div class="num">{{ $overallSuccessRate !== null ? $overallSuccessRate.'%' : '—' }}</div><div class="lbl">Success rate</div></div>
    <div class="stat"><div class="num">{{ $outcomeCoverage !== null ? $outcomeCoverage.'%' : '—' }}</div><div class="lbl">Outcome coverage</div></div>
    <div class="stat"><div class="num">{{ $adoptionRate !== null ? $adoptionRate.'%' : '—' }}</div><div class="lbl">Adoption rate</div></div>
</div>
<div class="card">
    <h2>Patterns</h2>
    <table>
        <tr><th>Challenge</th><th>Action</th><th>Cohort</th><th>Attempts</th><th>Success</th><th>Failures</th><th>Dismissed</th><th>Top failure reasons</th></tr>
        @foreach ($patterns as $p)
        <tr>
            <td><a href="{{ route('challenges.show', $p->challenge_key) }}">{{ $p->challenge_key }}</a></td>
            <td>{{ $p->actionMeasure?->name }}</td>
            <td>{{ $p->cohort }}</td>
            <td>{{ $p->attempts }}</td>
            <td class="conf">{{ $p->successRate() !== null ? $p->successRate().'%' : '—' }}</td>
            <td>{{ $p->failures }}</td>
            <td>{{ $p->dismissals }}</td>
            <td class="muted">{{ collect($p->failure_reasons ?? [])->sortDesc()->take(3)->map(fn ($c, $r) => "$r ($c)")->implode(', ') }}</td>
        </tr>
        @endforeach
        @if ($patterns->isEmpty())<tr><td colspan="8" class="muted">No patterns yet.</td></tr>@endif
    </table>
</div>
<div class="card">
    <h2>Coverage gaps <span class="muted">(signalled challenges with no measure)</span></h2>
    <table>
        <tr><th>Challenge</th><th>Signals</th><th>Companies</th><th></th></tr>
        @foreach ($coverageGaps as $g)
        <tr>
            <td>{{ $g->challenge_key }}</td>
            <td>{{ $g->signals }}</td>
            <td>{{ $g->companies }}</td>
            <td><a href="{{ route('measures.create') }}">Add a measure</a></td>
        </tr>
        @endforeach
        @if ($coverageGaps->isEmpty())<tr><td colspan="4" class="muted">Every signalled challenge has at least one measure in the catalog.</td></tr>@endif
    </table>
</div>
<div class="card">
    <h2>Confidence calibration <span class="muted">— predicted vs observed success</span></h2>
    <table>
        <tr><th>Confidence range</th><th>Recs</th><th>Predicted</th><th>Observed</th><th>Gap</th></tr>
        @foreach ($calibration as $row)
        <tr>
            <td>{{ $row['range'] }}</td>
            <td>{{ $row['count'] }}</td>
            <td class="conf">{{ $row['predicted'] }}%</td>
            <td class="conf">{{ $row['observed'] }}%</td>
            <td><span class="badge b-{{ abs($row['predicted'] - $row['observed']) <= 15 ? 'success' : 'failure' }}">{{ round($row['observed'] - $row['predicted'], 1) }}%</span></td>
        </tr>
        @endforeach
        @if ($calibration->isEmpty())<tr><td colspan="5" class="muted">No measured recommendations yet — calibration appears once outcomes exist.</td></tr>@endif
    </table>
</div>
@endsection
