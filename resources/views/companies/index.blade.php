@extends('layouts.app')
@section('content')
<h1>Companies</h1>
<div class="card">
    <table>
        <tr><th>Name</th><th>Source</th><th>Industry</th><th>Maturity</th><th>Situation</th><th>Signals</th><th>Recs</th></tr>
        @foreach ($companies as $c)
        <tr>
            <td><a href="{{ route('companies.show', $c) }}">{{ $c->name ?? $c->external_id }}</a></td>
            <td>{{ $c->source?->name }}</td>
            <td>{{ $c->industry ?? '—' }}</td>
            <td>{{ $c->maturity ?? '—' }}</td>
            <td class="muted">{{ implode(', ', $c->situation ?? []) }}</td>
            <td>{{ $c->signals_count }}</td>
            <td>{{ $c->recommendations_count }}</td>
        </tr>
        @endforeach
        @if ($companies->isEmpty())<tr><td colspan="7" class="muted">No companies yet.</td></tr>@endif
    </table>
    {{ $companies->links() }}
</div>
@endsection
