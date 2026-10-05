@extends('layouts.app')
@section('content')
<h1>Challenge: <code>{{ $challenge }}</code></h1>

<div class="stats">
    <div class="stat"><div class="num">{{ $signalCount }}</div><div class="lbl">Signals</div></div>
    <div class="stat"><div class="num">{{ $companyCount }}</div><div class="lbl">Companies affected</div></div>
    <div class="stat"><div class="num">{{ $patterns->count() }}</div><div class="lbl">Measures measured</div></div>
    <div class="stat"><div class="num">{{ $lastSeenAt?->diffForHumans() ?? '—' }}</div><div class="lbl">Last seen</div></div>
    <div class="stat"><div class="num">{{ $bestMeasure ? $bestMeasure->actionMeasure?->name : '—' }}</div><div class="lbl">Best proven measure {{ $bestMeasure?->effectiveRate() !== null ? '('.$bestMeasure->effectiveRate().'%)' : '' }}</div></div>
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
    <h2>Measures by cohort</h2>
    <table>
        <tr><th>Measure</th><th>Cohort</th><th>Attempts</th><th>Success</th><th>Failures</th><th>Top failure reasons</th></tr>
        @foreach ($patterns as $p)
        <tr>
            <td><a href="{{ route('measures.show', $p->actionMeasure) }}">{{ $p->actionMeasure?->name }}</a></td>
            <td>{{ $p->cohort }}</td>
            <td>{{ $p->attempts }}</td>
            <td class="conf">{{ $p->successRate() !== null ? $p->successRate().'%' : '—' }}</td>
            <td>{{ $p->failures }}</td>
            <td class="muted">{{ collect($p->failure_reasons ?? [])->sortDesc()->take(3)->map(fn ($c, $r) => "$r ($c)")->implode(', ') }}</td>
        </tr>
        @endforeach
        @if ($patterns->isEmpty())<tr><td colspan="6" class="muted">No measured measures for this challenge — a coverage gap worth closing.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Companies affected</h2>
    <table>
        <tr><th>Company</th><th>Signals</th></tr>
        @foreach ($byCompany as $row)
        <tr><td><a href="{{ route('companies.show', $row['company']) }}">{{ $row['company']?->name }}</a></td><td>{{ $row['count'] }}</td></tr>
        @endforeach
        @if ($byCompany->isEmpty())<tr><td colspan="2" class="muted">None.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Recent recommendations</h2>
    <table>
        <tr><th>Company</th><th>Recommended</th><th>Conf</th><th>Status</th><th>Created</th></tr>
        @foreach ($recommendations as $r)
        <tr>
            <td><a href="{{ route('companies.show', $r->company) }}">{{ $r->company?->name }}</a></td>
            <td>{{ $r->actionMeasure?->name }}</td>
            <td class="conf">{{ $r->confidence }}%</td>
            <td><span class="badge b-{{ $r->status }}">{{ $r->status }}</span></td>
            <td class="muted">{{ $r->created_at->diffForHumans() }}</td>
        </tr>
        @endforeach
        @if ($recommendations->isEmpty())<tr><td colspan="5" class="muted">No recommendations yet.</td></tr>@endif
    </table>
</div>
@endsection
