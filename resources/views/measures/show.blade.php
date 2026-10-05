@extends('layouts.app')
@section('content')
<h1>{{ $measure->name }}</h1>
<p class="muted" style="margin-bottom:1rem"><code>{{ $measure->key }}</code> · addresses: {{ implode(', ', $measure->addresses_challenges ?? []) ?: '—' }}</p>
<div class="stat-grid">
    <div class="stat"><div class="num">{{ $recommendations }}</div><div class="lbl">Recommendations</div></div>
    <div class="stat"><div class="num">{{ $outcomes }}</div><div class="lbl">Outcomes</div></div>
    <div class="stat"><div class="num">{{ $successRate !== null ? $successRate.'%' : '—' }}</div><div class="lbl">Success rate</div></div>
</div>
<div class="card">
    <h2>Pattern by cohort</h2>
    <table>
        <tr><th>Challenge</th><th>Cohort</th><th>Attempts</th><th>Success</th><th>Failures</th><th>Top failure reasons</th></tr>
        @foreach ($patterns as $p)
        <tr>
            <td>{{ $p->challenge_key }}</td>
            <td>{{ $p->cohort }}</td>
            <td>{{ $p->attempts }}</td>
            <td class="conf">{{ $p->successRate() !== null ? $p->successRate().'%' : '—' }}</td>
            <td>{{ $p->failures }}</td>
            <td class="muted">{{ collect($p->failure_reasons ?? [])->sortDesc()->take(3)->map(fn ($c, $r) => "$r ($c)")->implode(', ') }}</td>
        </tr>
        @endforeach
        @if ($patterns->isEmpty())<tr><td colspan="6" class="muted">No measured outcomes for this measure yet.</td></tr>@endif
    </table>
</div>
@endsection
