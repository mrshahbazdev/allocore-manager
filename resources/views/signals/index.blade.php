@extends('layouts.app')
@section('content')
<h1>Signals</h1>
<div class="card">
    <form method="GET" class="inline" style="margin-bottom:.75rem; display:flex; gap:.5rem">
        <select name="source_id" onchange="this.form.submit()">
            <option value="">All sources</option>
            @foreach ($sources as $src)
                <option value="{{ $src->id }}" {{ (string) $sourceId === (string) $src->id ? 'selected' : '' }}>{{ $src->name }}</option>
            @endforeach
        </select>
        <select name="type" onchange="this.form.submit()">
            <option value="">All types</option>
            @foreach ($types as $t)
                <option value="{{ $t }}" {{ $type === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        <select name="challenge" onchange="this.form.submit()">
            <option value="">All challenges</option>
            @foreach ($challenges as $c)
                <option value="{{ $c }}" {{ $challenge === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
        @if ($sourceId || $type || $challenge)
            <a href="{{ route('signals.index') }}">clear</a>
        @endif
    </form>
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
