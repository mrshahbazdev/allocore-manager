@extends('layouts.app')
@section('content')
<h1>Signals</h1>
<div class="card">
    <table>
        <tr><th>Occurred</th><th>Source</th><th>Type</th><th>Challenge</th><th>Company</th><th>User</th><th>Payload</th></tr>
        @foreach ($signals as $s)
        <tr>
            <td class="muted">{{ $s->occurred_at?->diffForHumans() }}</td>
            <td><a href="{{ route('intelligence.platform', $s->source) }}">{{ $s->source?->name }}</a></td>
            <td><code>{{ $s->type }}</code></td>
            <td>@if ($s->challenge_key)<a href="{{ route('challenges.show', $s->challenge_key) }}"><code>{{ $s->challenge_key }}</code></a>@else —@endif</td>
            <td>@if ($s->company)<a href="{{ route('companies.show', $s->company) }}">{{ $s->company->name }}</a>@else —@endif</td>
            <td class="muted">{{ $s->external_user_id ?? '—' }}</td>
            <td class="muted">{{ $s->payload ? json_encode($s->payload) : '—' }}</td>
        </tr>
        @endforeach
        @if ($signals->isEmpty())<tr><td colspan="7" class="muted">No signals yet — <a href="{{ route('signals.create') }}">record one</a>.</td></tr>@endif
    </table>
    {{ $signals->links() }}
</div>
@endsection
