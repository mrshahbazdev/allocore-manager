@extends('layouts.app')
@section('content')
<h1>{{ $company->name ?? $company->external_id }} — Timeline</h1>
<p class="muted" style="margin-bottom:1rem">
    Unified history: signals received, recommendations issued, outcomes measured.
    · <a href="{{ route('companies.show', $company) }}">Back to company</a>
</p>

@if ($events->isEmpty())
<div class="card"><p class="muted">No activity recorded yet.</p></div>
@else
<div class="card">
    <table>
        <tr><th>When</th><th>Kind</th><th>What</th><th>Detail</th></tr>
        @foreach ($events as $e)
        <tr>
            <td class="muted" style="white-space:nowrap">{{ $e['at']->diffForHumans() }}</td>
            <td><span class="badge b-{{ $e['kind'] === 'signal' ? 'pending' : ($e['kind'] === 'recommendation' ? 'accepted' : 'success') }}">{{ $e['kind'] }}</span></td>
            <td>{{ $e['label'] }}</td>
            <td class="muted">{{ $e['detail'] }}</td>
        </tr>
        @endforeach
    </table>
</div>
@endif
@endsection
