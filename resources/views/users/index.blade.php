@extends('layouts.app')
@section('content')
<h1>Users</h1>
<div class="card">
    <form method="GET" style="margin-bottom:.75rem">
        <select name="source_id">
            <option value="">All sources</option>
            @foreach ($sources as $src)
                <option value="{{ $src->id }}" @selected((string) $sourceId === (string) $src->id)>{{ $src->name }}</option>
            @endforeach
        </select>
        <button>Filter</button>
        <a href="{{ route('users.index') }}">clear</a>
    </form>
    <table>
        <tr><th>User (platform id)</th><th>Source</th><th>Signals</th><th>Last seen</th></tr>
        @foreach ($users as $u)
        <tr>
            <td><a href="{{ route('users.show', $u->external_user_id) }}">{{ $u->external_user_id }}</a></td>
            <td>{{ $u->source?->name }}</td>
            <td>{{ $u->total }}</td>
            <td class="muted">{{ \Carbon\Carbon::parse($u->last_seen)->diffForHumans() }}</td>
        </tr>
        @endforeach
        @if ($users->isEmpty())<tr><td colspan="4" class="muted">No user-attributed signals yet.</td></tr>@endif
    </table>
    {{ $users->links() }}
</div>
@endsection
