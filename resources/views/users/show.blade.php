@extends('layouts.app')
@section('content')
<h1>User {{ $externalUserId }}</h1>
<div class="stat-grid">
    <div class="stat"><div class="num">{{ $signals->total() }}</div><div class="lbl">Signals</div></div>
    <div class="stat"><div class="num">{{ $companyCount }}</div><div class="lbl">Companies touched</div></div>
    <div class="stat"><div class="num">{{ $topChallenges->first()?->challenge_key ?? '—' }}</div><div class="lbl">Top challenge{{ $topChallenges->first() ? ' ('.$topChallenges->first()->total.')' : '' }}</div></div>
</div>
<div class="card">
    <h2>Best next actions for their companies</h2>
    <table>
        <tr><th>Company</th><th>Action</th><th>Challenge</th><th>Confidence</th></tr>
        @foreach ($recommendations as $r)
        <tr>
            <td><a href="{{ route('companies.show', $r->company) }}">{{ $r->company?->name ?? $r->company?->external_id }}</a></td>
            <td>{{ $r->actionMeasure?->name }}</td>
            <td>{{ $r->challenge_key }}</td>
            <td class="conf">{{ $r->confidence !== null ? $r->confidence.'%' : '—' }}</td>
        </tr>
        @endforeach
        @if ($recommendations->isEmpty())<tr><td colspan="4" class="muted">No pending actions for this user's companies.</td></tr>@endif
    </table>
</div>
<div class="card">
    <h2>Activity</h2>
    <table>
        <tr><th>When</th><th>Source</th><th>Type</th><th>Challenge</th></tr>
        @foreach ($signals as $s)
        <tr>
            <td class="muted">{{ $s->occurred_at->diffForHumans() }}</td>
            <td>{{ $s->source?->name }}</td>
            <td>{{ $s->type }}</td>
            <td>{{ $s->challenge_key ?? '—' }}</td>
        </tr>
        @endforeach
    </table>
    {{ $signals->links() }}
</div>
@endsection
