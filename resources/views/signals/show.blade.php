@extends('layouts.app')
@section('content')
<h1>Signal #{{ $signal->id }}</h1>

<div class="card">
    <table>
        <tr><th>Type</th><td><code>{{ $signal->type }}</code></td></tr>
        <tr><th>Challenge</th><td>@if ($signal->challenge_key)<a href="{{ route('challenges.show', $signal->challenge_key) }}">{{ $signal->challenge_key }}</a>@else — @endif</td></tr>
        <tr><th>Source</th><td>{{ $signal->source?->name }}</td></tr>
        <tr><th>Company</th><td>@if ($signal->company)<a href="{{ route('companies.show', $signal->company) }}">{{ $signal->company->name ?? $signal->company->external_id }}</a>@else — @endif</td></tr>
        <tr><th>External user</th><td>{{ $signal->external_user_id ?? '—' }}</td></tr>
        <tr><th>Occurred</th><td>{{ $signal->occurred_at->toDateTimeString() }} ({{ $signal->occurred_at->diffForHumans() }})</td></tr>
        <tr><th>Ingested</th><td>{{ $signal->created_at->toDateTimeString() }}</td></tr>
    </table>
</div>

<div class="card">
    <h2>Payload</h2>
    @if (empty($signal->payload))
        <p class="muted">No payload — raw form signal.</p>
    @else
        <pre style="font-size:.8rem;white-space:pre-wrap">{{ json_encode($signal->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
    @endif
</div>

@if ($recommendations->isNotEmpty())
<div class="card">
    <h2>Recommendations generated from this signal</h2>
    <table>
        <tr><th>Action</th><th>Confidence</th><th>Status</th><th>Outcome</th><th></th></tr>
        @foreach ($recommendations as $r)
        <tr>
            <td>{{ $r->actionMeasure?->name }}</td>
            <td class="conf">{{ $r->confidence !== null ? $r->confidence.'%' : '—' }}</td>
            <td><span class="badge b-{{ $r->status }}">{{ $r->status }}</span></td>
            <td>@if ($r->outcome)<span class="badge b-{{ $r->outcome->result }}">{{ $r->outcome->result }}</span>@else —@endif</td>
            <td><a href="{{ route('recommendations.show', $r) }}">view</a></td>
        </tr>
        @endforeach
    </table>
</div>
@endif
@endsection
