@extends('layouts.app')
@section('content')
<h1>Trend detection</h1>
<p class="muted" style="margin-bottom:1rem">Challenge volume in the last {{ $window }} days vs the prior {{ $window }} days — trends the system spots before users do.</p>
<div class="card">
    <form method="GET" style="margin-bottom:.75rem">
        Window:
        @foreach ([7, 14, 30, 60, 90] as $d)
            <a href="?days={{ $d }}" class="badge {{ $window === $d ? 'b-accepted' : 'b-pending' }}">{{ $d }}d</a>
        @endforeach
    </form>
    <table>
        <tr><th>Challenge</th><th>Recent</th><th>Previous</th><th>Change</th><th>Direction</th></tr>

        @foreach ($trends as $t)
        <tr>
            <td><a href="{{ route('challenges.show', $t['challenge_key']) }}">{{ $t['challenge_key'] }}</a></td>
            <td>{{ $t['recent'] }}</td>
            <td>{{ $t['previous'] }}</td>
            <td class="{{ $t['direction'] === 'rising' ? 'conf' : 'muted' }}">
                {{ $t['growth_pct'] === null ? 'new' : ($t['growth_pct'] > 0 ? '+' : '').$t['growth_pct'].'%' }}
            </td>
            <td>
                @if ($t['direction'] === 'rising')<span class="badge b-failure">rising</span>
                @elseif ($t['direction'] === 'falling')<span class="badge b-success">falling</span>
                @else<span class="badge b-pending">stable</span>@endif
            </td>
        </tr>
        @endforeach
        @if ($trends->isEmpty())<tr><td colspan="5" class="muted">No challenge signals in this window.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Brand-new challenges (first ever sighting in the last 7 days)</h2>
    <table>
        <tr><th>Challenge</th><th>Signals (7d)</th><th>First seen</th></tr>
        @foreach ($emerging as $e)
        <tr>
            <td><a href="{{ route('challenges.show', $e->challenge_key) }}">{{ $e->challenge_key }}</a></td>
            <td>{{ $e->recent }}</td>
            <td class="muted">{{ \Illuminate\Support\Carbon::parse($e->first_seen)->diffForHumans() }}</td>
        </tr>
        @endforeach
        @if ($emerging->isEmpty())<tr><td colspan="3" class="muted">Nothing brand-new this week.</td></tr>@endif
    </table>
</div>


@if ($goneQuiet->isNotEmpty())
<div class="card">
    <h2>Gone quiet <span class="muted">(active before, zero signals in 14 days)</span></h2>
    <table>
        <tr><th>Challenge</th><th>Signals (prior 60d)</th></tr>
        @foreach ($goneQuiet as $q)
        <tr>
            <td><a href="{{ route('challenges.show', $q['challenge_key']) }}">{{ $q['challenge_key'] }}</a></td>
            <td>{{ $q['previous'] }}</td>
        </tr>
        @endforeach
    </table>
    <p class="muted" style="margin-top:.4rem">Resolved or simply unobserved — worth confirming before treating as fixed.</p>
</div>
@endif
@endsection
