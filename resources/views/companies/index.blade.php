@extends('layouts.app')
@section('content')
<h1>Companies</h1>
<div class="card">
    <table>
        <tr><th>Name</th><th>Source</th><th>Industry</th><th>Maturity</th><th>Situation</th><th>Signals</th><th>Recs</th><th>Needs</th></tr>
        @foreach ($companies as $c)
        <tr>
            <td><a href="{{ route('companies.show', $c) }}">{{ $c->name ?? $c->external_id }}</a></td>
            <td>{{ $c->source?->name }}</td>
            <td>{{ $c->industry ?? '—' }}</td>
            <td>{{ $c->maturity ?? '—' }}</td>
            <td class="muted">{{ implode(', ', $c->situation ?? []) }}</td>
            <td>{{ $c->signals_count }}</td>
            <td>{{ $c->recommendations_count }}</td>
            <td>
                @if ($c->pending_recs_count)<span class="badge b-pending">{{ $c->pending_recs_count }} decision{{ $c->pending_recs_count > 1 ? 's' : '' }}</span>@endif
                @if ($c->unmeasured_count)<span class="badge b-partial">{{ $c->unmeasured_count }} unmeasured</span>@endif
            </td>
        </tr>
        @endforeach
        @if ($companies->isEmpty())<tr><td colspan="7" class="muted">No companies yet.</td></tr>@endif
    </table>
    {{ $companies->links() }}
</div>
@endsection
