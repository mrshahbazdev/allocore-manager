@extends('layouts.app')
@section('content')
<h1>Company clusters</h1>
<p class="muted" style="margin-bottom:1rem">Companies grouped by situation + maturity cohort — "similar" means similar challenges and stage, not industry alone.</p>
@foreach ($clusters as $cluster)
<div class="card">
    <h2>{{ $cluster['cohort'] }} <span class="muted">({{ $cluster['companies']->count() }} companies)</span></h2>
    <table>
        <tr><th>Company</th><th>Source</th><th>Industry</th><th>Situation</th></tr>
        @foreach ($cluster['companies'] as $c)
        <tr>
            <td><a href="{{ route('companies.show', $c) }}">{{ $c->name ?? $c->external_id }}</a></td>
            <td>{{ $c->source?->name }}</td>
            <td>{{ $c->industry ?? '—' }}</td>
            <td class="muted">{{ implode(', ', $c->situation ?? []) }}</td>
        </tr>
        @endforeach
    </table>
</div>
@endforeach
@if ($clusters->isEmpty())<div class="card muted">No companies yet.</div>@endif
@endsection
