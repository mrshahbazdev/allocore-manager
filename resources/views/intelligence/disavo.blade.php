@extends('layouts.app')
@section('content')
<h1>DISAVO — strategic view</h1>
<div class="card">
    <h2>Ecosystem</h2>
    <div class="stat-grid">
        <div class="stat"><div class="num">{{ $platformsConnected }}</div><div class="lbl">Platforms connected</div></div>
        <div class="stat"><div class="num">{{ $companiesCovered }}</div><div class="lbl">Companies covered</div></div>
        <div class="stat"><div class="num">{{ $signalsProcessed }}</div><div class="lbl">Signals processed</div></div>
    </div>
</div>
<div class="card">
    <h2>Performance</h2>
    <div class="stat-grid">
        <div class="stat"><div class="num">{{ $recommendationsIssued }}</div><div class="lbl">Recommendations issued</div></div>
        <div class="stat"><div class="num">{{ $outcomesMeasured }}</div><div class="lbl">Outcomes measured</div></div>
        <div class="stat"><div class="num">{{ $successRate !== null ? $successRate.'%' : '—' }}</div><div class="lbl">Success rate</div></div>
    </div>
</div>
<div class="card">
    <h2>Emerging risks (last 30 days)</h2>
    <table>
        <tr><th>Challenge</th><th>Signals</th></tr>
        @foreach ($emergingRisks as $row)
        <tr><td>{{ $row->challenge_key }}</td><td>{{ $row->total }}</td></tr>
        @endforeach
        @if ($emergingRisks->isEmpty())<tr><td colspan="2" class="muted">No risks detected in the last 30 days.</td></tr>@endif
    </table>
</div>
@endsection
