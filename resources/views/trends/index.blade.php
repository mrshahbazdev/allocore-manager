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
            <td>{{ $t['challenge_key'] }}</td>
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
@endsection
