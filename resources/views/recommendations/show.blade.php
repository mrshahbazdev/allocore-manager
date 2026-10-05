@extends('layouts.app')
@section('content')
@php $r = $recommendation; @endphp
<h1>Recommendation #{{ $r->id }}</h1>

<div class="card">
    <table>
        <tr><th>Company</th><td>@if ($r->company)<a href="{{ route('companies.show', $r->company) }}">{{ $r->company->name ?? $r->company->external_id }}</a>@else — @endif</td></tr>
        <tr><th>Challenge</th><td><a href="{{ route('challenges.show', $r->challenge_key) }}">{{ $r->challenge_key }}</a></td></tr>
        <tr><th>Action</th><td>@if ($r->actionMeasure)<a href="{{ route('measures.show', $r->actionMeasure) }}">{{ $r->actionMeasure->name }}</a>@else — @endif</td></tr>
        <tr><th>Confidence</th><td class="conf">{{ $r->confidence !== null ? $r->confidence.'%' : '—' }}</td></tr>
        <tr><th>Status</th><td><span class="badge b-{{ $r->status }}">{{ $r->status }}</span></td></tr>
        <tr><th>Created</th><td>{{ $r->created_at->toDateTimeString() }} ({{ $r->created_at->diffForHumans() }})</td></tr>
        <tr><th>Trigger signal</th><td>@if ($r->signal)<code>{{ $r->signal->type }}</code> · {{ $r->signal->occurred_at->diffForHumans() }}@else — @endif</td></tr>
    </table>
</div>

<div class="card">
    <h2>Rationale</h2>
    @if (empty($r->rationale))
        <p class="muted">No rationale stored.</p>
    @else
        <p>{{ $r->rationale['message'] ?? '' }}</p>
        <pre style="font-size:.8rem;white-space:pre-wrap">{{ json_encode($r->rationale, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
    @endif
</div>

@if ($alternatives->isNotEmpty())
<div class="card">
    <h2>Alternative actions for this challenge</h2>
    <table>
        <tr><th>Measure</th><th>Description</th></tr>
        @foreach ($alternatives as $m)
        <tr><td><a href="{{ route('measures.show', $m) }}">{{ $m->name }}</a></td><td class="muted">{{ $m->description ?? '—' }}</td></tr>
        @endforeach
    </table>
</div>
@endif

<div class="card">
    <h2>Outcome</h2>
    @if ($r->outcome)
        <table>
            <tr><th>Result</th><td><span class="badge b-{{ $r->outcome->result }}">{{ $r->outcome->result }}</span></td></tr>
            <tr><th>Measured</th><td>{{ $r->outcome->measured_at->toDateTimeString() }} ({{ $r->outcome->measured_at->diffForHumans() }})</td></tr>
            @if ($r->outcome->failure_reason)<tr><th>Failure reason</th><td><code>{{ $r->outcome->failure_reason }}</code></td></tr>@endif
            @if (! empty($r->outcome->metrics))<tr><th>Metrics</th><td><pre style="font-size:.8rem">{{ json_encode($r->outcome->metrics, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></td></tr>@endif
        </table>
    @elseif (in_array($r->status, ['accepted', 'implemented']))
        <p class="muted">Not measured yet.</p>
        <a href="{{ route('recommendations.outcome.edit', $r) }}"><button>Measure outcome</button></a>
    @else
        <p class="muted">Outcome only applies to accepted/implemented recommendations.</p>
    @endif
</div>
@endsection
