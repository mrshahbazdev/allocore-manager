@extends('layouts.app')
@section('content')
<h1>{{ $source->name }} — product intelligence</h1>
<div class="card">
    <h2>Most common challenges</h2>
    <table>
        <tr><th>Challenge</th><th>Signals</th></tr>
        @foreach ($challengeCounts as $row)
        <tr><td>{{ $row->challenge_key }}</td><td>{{ $row->total }}</td></tr>
        @endforeach
        @if ($challengeCounts->isEmpty())<tr><td colspan="2" class="muted">No challenges recorded.</td></tr>@endif
    </table>
</div>
<div class="card">
    <h2>Rising challenges (last 30 days) — feature improvement opportunities</h2>
    <table>
        <tr><th>Challenge</th><th>Signals</th></tr>
        @foreach ($risingChallenges as $row)
        <tr><td>{{ $row->challenge_key }}</td><td>{{ $row->total }}</td></tr>
        @endforeach
        @if ($risingChallenges->isEmpty())<tr><td colspan="2" class="muted">Nothing rising recently.</td></tr>@endif
    </table>
</div>
<div class="card">
    <h2>Most active users</h2>
    <table>
        <tr><th>User</th><th>Signals</th></tr>
        @foreach ($topUsers as $row)
        <tr><td>{{ $row->external_user_id }}</td><td>{{ $row->total }}</td></tr>
        @endforeach
        @if ($topUsers->isEmpty())<tr><td colspan="2" class="muted">No user-attributed signals yet.</td></tr>@endif
    </table>
</div>
<div class="card">
    <h2>Company health</h2>
    <table>
        <tr><th>Company</th><th>Signals (30d)</th><th>Top challenge</th><th>Pending recs</th><th>Unmeasured</th><th>Last signal</th></tr>
        @foreach ($companies as $c)
        <tr>
            <td><a href="{{ route('companies.show', $c) }}">{{ $c->name ?? $c->external_id }}</a></td>
            <td>{{ $c->signals_30d }}</td>
            <td>{{ $c->top_challenge ?? '—' }}</td>
            <td>{{ $c->pending_recs }}</td>
            <td>{{ $c->unmeasured }}</td>
            <td class="muted">{{ $c->signals_max_occurred_at ? \Carbon\Carbon::parse($c->signals_max_occurred_at)->diffForHumans() : '—' }}</td>
        </tr>
        @endforeach
        @if ($companies->isEmpty())<tr><td colspan="6" class="muted">No companies on this platform yet.</td></tr>@endif
    </table>
</div>
<div class="card">
    <h2>Recommendation effectiveness</h2>
    @foreach (['success' => 'Succeeded', 'partial' => 'Partially succeeded', 'failure' => 'Failed'] as $key => $label)
        <p>{{ $label }}: <strong>{{ $effectiveness[$key] ?? 0 }}</strong></p>
    @endforeach
</div>
@endsection
