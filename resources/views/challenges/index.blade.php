@extends('layouts.app')
@section('content')
<h1>Challenges</h1>
<div class="card">
    <form method="GET" action="{{ route('challenges.index') }}" style="margin-bottom:.75rem">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Filter challenge keys…" style="max-width:22rem">
    </form>
    <table>
        <tr><th>Key</th><th>Signals</th><th>Companies</th><th>Last seen</th><th>Coverage</th></tr>
        @foreach ($challenges as $c)
        <tr>
            <td><a href="{{ route('challenges.show', $c->challenge_key) }}"><code>{{ $c->challenge_key }}</code></a></td>
            <td>{{ $c->signals }}</td>
            <td>{{ $c->companies }}</td>
            <td class="muted">{{ \Illuminate\Support\Carbon::parse($c->last_seen)->diffForHumans() }}</td>
            <td>
                @if ($covered->contains($c->challenge_key))
                    <span class="badge b-success">covered</span>
                @else
                    <span class="badge b-failure">gap</span>
                    <a href="{{ route('measures.create', ['challenge' => $c->challenge_key]) }}">add measure</a>
                @endif
            </td>
        </tr>
        @endforeach
        @if ($challenges->isEmpty())<tr><td colspan="5" class="muted">No challenges signalled yet.</td></tr>@endif
    </table>
    <p class="muted">"gap" = companies are signalling this challenge but no active measure addresses it.</p>
</div>
@endsection
