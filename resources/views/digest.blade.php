@extends('layouts.app')
@section('content')
<h1>Daily digest <span class="muted">(last {{ $days === 1 ? '24 hours' : $days.' days' }})</span></h1>
<p style="margin-bottom:1rem">
    @foreach ([1, 3, 7, 14] as $d)
        <a href="?days={{ $d }}" class="badge {{ $days === $d ? 'b-accepted' : 'b-pending' }}">{{ $d }}d</a>
    @endforeach
</p>

<div class="stat-grid">
    <div class="stat"><div class="num">{{ $signals }}</div><div class="lbl">New signals</div></div>
    <div class="stat"><div class="num">{{ $newRecs->count() }}</div><div class="lbl">New recommendations</div></div>
    <div class="stat"><div class="num">{{ $newOutcomes->count() }}</div><div class="lbl">Outcomes measured</div></div>
    <div class="stat"><div class="num">{{ $pendingCount }}</div><div class="lbl">Decisions waiting</div></div>
    <div class="stat"><div class="num">{{ $unmeasuredCount }}</div><div class="lbl">Outcomes missing</div></div>
    <div class="stat"><div class="num">{{ $newCompanies }}</div><div class="lbl">New companies</div></div>
</div>

<div class="card">
    <h2>Activity by source</h2>
    <table>
        <tr><th>Source</th><th>Signals</th></tr>
        @foreach ($bySource as $row)
        <tr><td><a href="{{ route('sources.show', $row->source_id) }}">{{ $row->source?->name ?? 'unknown' }}</a></td><td>{{ $row->total }}</td></tr>
        @endforeach
        @if ($bySource->isEmpty())<tr><td colspan="2" class="muted">No signals in this window.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Most active challenges</h2>
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
