@extends('layouts.app')
@section('content')
<h1>Data providers</h1>
<div class="card">
    <a href="{{ route('sources.create') }}"><button>Register source</button></a>
    <table style="margin-top:.75rem">
        <tr><th>Key</th><th>Name</th><th>Type</th><th>Companies</th><th>Signals</th><th>Status</th><th></th></tr>
        @foreach ($sources as $s)
        <tr>
            <td><code>{{ $s->key }}</code></td>
            <td>{{ $s->name }}</td>
            <td>{{ $s->type }}</td>
            <td>{{ $s->companies_count }}</td>
            <td>{{ $s->signals_count }}</td>
            <td><span class="badge b-{{ $s->is_active ? 'success' : 'dismissed' }}">{{ $s->is_active ? 'active' : 'inactive' }}</span></td>
            <td><a href="{{ route('sources.edit', $s) }}">Edit</a> · <a href="{{ route('sources.import', $s) }}">Import</a> · <a href="{{ route('intelligence.platform', $s) }}">Intelligence</a></td>
        </tr>
        @endforeach
    </table>
</div>
@endsection
