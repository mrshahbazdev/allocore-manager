@extends('layouts.app')
@section('content')
<h1>{{ $source->name }} — product intelligence</h1>
<div class="card">
    <h2>Most common challenges</h2>
    <table>
        <tr><th>Challenge</th><th>Signals</th></tr>
        @foreach ($challengeCounts as $row)
        <tr><td>{{ $row->challenge_key }}</td><td>{{ $row->total }}</td></tr>
        @endforeach
        @if ($challengeCounts->isEmpty())<tr><td colspan="2" class="muted">No challenges recorded.</td></tr>@endif
    </table>
</div>
<div class="card">
    <h2>Recommendation effectiveness</h2>
    @foreach (['success' => 'Succeeded', 'partial' => 'Partially succeeded', 'failure' => 'Failed'] as $key => $label)
        <p>{{ $label }}: <strong>{{ $effectiveness[$key] ?? 0 }}</strong></p>
    @endforeach
</div>
@endsection
