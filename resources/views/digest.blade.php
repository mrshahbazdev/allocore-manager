@extends('layouts.app')
@section('content')
<h1>Daily digest <span class="muted">(last 24 hours)</span></h1>

<div class="stat-grid">
    <div class="stat"><div class="num">{{ $signals }}</div><div class="lbl">New signals</div></div>
    <div class="stat"><div class="num">{{ $newRecs->count() }}</div><div class="lbl">New recommendations</div></div>
    <div class="stat"><div class="num">{{ $newOutcomes->count() }}</div><div class="lbl">Outcomes measured</div></div>
    <div class="stat"><div class="num">{{ $pendingCount }}</div><div class="lbl">Decisions waiting</div></div>
    <div class="stat"><div class="num">{{ $unmeasuredCount }}</div><div class="lbl">Outcomes missing</div></div>
</div>

<div class="card">
    <h2>Most active challenges (24h)</h2>
    <table>
        <tr><th>Challenge</th><th>Signals</th></tr>
        @foreach ($topChallenges as $c)
        <tr><td>{{ $c->challenge_key }}</td><td>{{ $c->total }}</td></tr>
        @endforeach
        @if ($topChallenges->isEmpty())<tr><td colspan="2" class="muted">Quiet day — no new signals.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Recommendations issued (24h)</h2>
    <table>
        <tr><th>Company</th><th>Action</th><th>Challenge</th><th>Confidence</th></tr>
        @foreach ($newRecs as $r)
        <tr>
            <td><a href="{{ route('companies.show', $r->company) }}">{{ $r->company->name ?? $r->company->external_id }}</a></td>
            <td>{{ $r->actionMeasure?->name }}</td>
            <td>{{ $r->challenge_key }}</td>
            <td class="conf">{{ $r->confidence !== null ? number_format($r->confidence, 0).'%' : '—' }}</td>
        </tr>
        @endforeach
        @if ($newRecs->isEmpty())<tr><td colspan="4" class="muted">None in the last day.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Outcomes measured (24h)</h2>
    <table>
        <tr><th>Company</th><th>Measure</th><th>Result</th><th>Failure reason</th></tr>
        @foreach ($newOutcomes as $o)
        <tr>
            <td>{{ $o->recommendation?->company?->name ?? $o->recommendation?->company?->external_id }}</td>
            <td>{{ $o->recommendation?->actionMeasure?->name }}</td>
            <td><span class="badge b-{{ $o->result }}">{{ $o->result }}</span></td>
            <td class="muted">{{ $o->failure_reason ?? '—' }}</td>
        </tr>
        @endforeach
        @if ($newOutcomes->isEmpty())<tr><td colspan="4" class="muted">None in the last day.</td></tr>@endif
    </table>
</div>
@endsection
