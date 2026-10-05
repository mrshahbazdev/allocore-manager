@extends('layouts.app')
@section('content')
<h1>Users</h1>
<div class="card">
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
