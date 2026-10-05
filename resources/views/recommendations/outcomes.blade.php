@extends('layouts.app')
@section('content')
<h1>Measured outcomes</h1>
<div class="stats">
    <div class="stat"><div class="num">{{ $byResult['success'] ?? 0 }}</div><div class="lbl">Success</div></div>
    <div class="stat"><div class="num">{{ $byResult['partial'] ?? 0 }}</div><div class="lbl">Partial</div></div>
    <div class="stat"><div class="num">{{ $byResult['failure'] ?? 0 }}</div><div class="lbl">Failure</div></div>
</div>
<div class="card">
    <table>
        <tr><th>Measured</th><th>Company</th><th>Challenge</th><th>Measure</th><th>Result</th><th>Why it failed</th></tr>
        @foreach ($outcomes as $o)
        <tr>
            <td class="muted">{{ $o->measured_at?->diffForHumans() }}</td>
            <td><a href="{{ route('companies.show', $o->recommendation?->company) }}">{{ $o->recommendation?->company?->name }}</a></td>
            <td><a href="{{ route('challenges.show', $o->recommendation?->challenge_key) }}"><code>{{ $o->recommendation?->challenge_key }}</code></a></td>
            <td>{{ $o->recommendation?->actionMeasure?->name }}</td>
            <td><span class="badge b-{{ $o->result }}">{{ $o->result }}</span></td>
            <td class="muted">{{ $o->failure_reason ?? '—' }}</td>
        </tr>
        @endforeach
        @if ($outcomes->isEmpty())<tr><td colspan="6" class="muted">No outcomes measured yet — this is what the loop learns from.</td></tr>@endif
    </table>
    {{ $outcomes->links() }}
</div>
@endsection
