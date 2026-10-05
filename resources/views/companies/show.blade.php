@extends('layouts.app')
@section('content')
<h1>{{ $company->name ?? $company->external_id }}</h1>
<p class="muted" style="margin-bottom:1rem">
    {{ $company->source?->name }} · {{ $company->industry ?? 'no industry' }} · {{ $company->maturity ?? 'maturity unknown' }}
    @if ($company->situation) · {{ implode(', ', $company->situation) }}@endif
</p>

@if ($bestNextAction)
<div class="card" style="border:2px solid #166534">
    <h2>Best next action</h2>
    <p style="font-size:1.1rem;font-weight:700;margin:.25rem 0">{{ $bestNextAction->actionMeasure?->name }}</p>
    <p class="muted">{{ $bestNextAction->rationale['message'] ?? '' }}</p>
    <p class="conf" style="margin-top:.25rem">{{ $bestNextAction->confidence }}% confidence · {{ $bestNextAction->challenge_key }}</p>
</div>
@endif
<div class="card">
    <h2>Recommended next actions</h2>
    <form method="POST" action="{{ route('companies.refresh', $company) }}" class="inline" style="margin-bottom:.75rem">
        @csrf
        <button class="secondary">Re-evaluate challenges</button>
    </form>
    <table>
        <tr><th>Challenge</th><th>Action</th><th>Confidence</th><th>Why</th><th>Status</th><th></th></tr>
        @foreach ($recommendations as $r)
        <tr>
            <td>{{ $r->challenge_key }}</td>
            <td>{{ $r->actionMeasure?->name }}</td>
            <td class="conf">{{ $r->confidence !== null ? $r->confidence.'%' : '—' }}</td>
            <td class="muted">{{ $r->rationale['message'] ?? '' }}</td>
            <td><span class="badge b-{{ $r->status }}">{{ $r->status }}</span>
                @if ($r->outcome)<span class="badge b-{{ $r->outcome->result }}">{{ $r->outcome->result }}</span>@endif
            </td>
            <td style="white-space:nowrap">
                @if ($r->status === 'pending')
                <form class="inline" method="POST" action="{{ route('recommendations.update', $r) }}">@csrf @method('PATCH')
                    <input type="hidden" name="status" value="accepted"><button>Accept</button></form>
                <form class="inline" method="POST" action="{{ route('recommendations.update', $r) }}">@csrf @method('PATCH')
                    <input type="hidden" name="status" value="dismissed"><button class="secondary">Dismiss</button></form>
                @endif
                @if (in_array($r->status, ['accepted', 'implemented']) && ! $r->outcome)
                <form class="inline" method="POST" action="{{ route('recommendations.outcome', $r) }}">@csrf
                    <input type="hidden" name="result" value="success"><button>Success</button></form>
                <form class="inline" method="POST" action="{{ route('recommendations.outcome', $r) }}">@csrf
                    <input type="hidden" name="result" value="failure"><button class="secondary">Failed</button></form>
                @endif
            </td>
        </tr>
        @endforeach
        @if ($recommendations->isEmpty())<tr><td colspan="6" class="muted">No recommendations yet — record a challenge signal first.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Similar companies</h2>
    <table>
        <tr><th>Company</th><th>Similarity</th><th>Situation</th></tr>
        @foreach ($similar as $row)
        <tr>
            <td><a href="{{ route('companies.show', $row['company']) }}">{{ $row['company']->name ?? $row['company']->external_id }}</a></td>
            <td>{{ $row['score'] }}%</td>
            <td class="muted">{{ implode(', ', $row['company']->situation ?? []) }}</td>
        </tr>
        @endforeach
        @if ($similar->isEmpty())<tr><td colspan="3" class="muted">No similar companies found yet.</td></tr>@endif
    </table>
</div>

<div class="card">
    <h2>Recent signals</h2>
    <table>
        <tr><th>When</th><th>Type</th><th>Challenge</th></tr>
        @foreach ($signals as $sig)
        <tr>
            <td class="muted">{{ $sig->occurred_at->diffForHumans() }}</td>
            <td>{{ $sig->type }}</td>
            <td>{{ $sig->challenge_key ?? '—' }}</td>
        </tr>
        @endforeach
        @if ($signals->isEmpty())<tr><td colspan="3" class="muted">None yet.</td></tr>@endif
    </table>
</div>
@endsection
