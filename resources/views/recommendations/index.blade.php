@extends('layouts.app')
@section('content')
<h1>Recommendations</h1>
<div class="card">
    <p>
        Filter:
        @foreach (['pending', 'accepted', 'implemented', 'dismissed'] as $s)
            <a href="{{ route('recommendations.index', array_filter(['status' => $s, 'challenge' => $challenge, 'company_id' => $companyId])) }}" class="{{ $status === $s ? 'badge b-'.$s : 'muted' }}">{{ $s }}</a>
        @endforeach
        · <a href="{{ route('recommendations.index', array_filter(['challenge' => $challenge, 'company_id' => $companyId])) }}" class="{{ $status ? 'muted' : '' }}">all</a>
    </p>
    <form method="GET" class="inline" style="margin-bottom:.75rem">
        @if ($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <select name="challenge" onchange="this.form.submit()">
            <option value="">All challenges</option>
            @foreach ($challenges as $c)
                <option value="{{ $c }}" {{ $challenge === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
        @if ($challenge || $companyId)
            <a href="{{ route('recommendations.index', array_filter(['status' => $status])) }}">clear filters</a>
        @endif
    </form>
    <table>
        <tr><th>Company</th><th>Challenge</th><th>Recommended</th><th>Conf</th><th>Status</th><th>Outcome</th><th>Created</th><th></th></tr>
        @foreach ($recommendations as $r)
        <tr>
            <td><a href="{{ route('companies.show', $r->company) }}">{{ $r->company?->name }}</a></td>
            <td><a href="{{ route('challenges.show', $r->challenge_key) }}"><code>{{ $r->challenge_key }}</code></a></td>
            <td><a href="{{ route('measures.show', $r->actionMeasure) }}">{{ $r->actionMeasure?->name }}</a></td>
            <td class="conf">{{ $r->confidence }}%</td>
            <td><span class="badge b-{{ $r->status }}">{{ $r->status }}</span></td>
            <td>
                @if ($r->outcome)
                    <span class="badge b-{{ $r->outcome->result }}">{{ $r->outcome->result }}</span>
                @elseif (in_array($r->status, ['accepted', 'implemented']))
                    <a href="{{ route('recommendations.outcome.edit', $r) }}">measure</a>
                @else
                    —
                @endif
            </td>
            <td class="muted">{{ $r->created_at->diffForHumans() }}</td>
            <td><a href="{{ route('recommendations.show', $r) }}">view</a></td>
        </tr>
        @endforeach
        @if ($recommendations->isEmpty())<tr><td colspan="8" class="muted">None{{ $status ? " with status {$status}" : '' }}.</td></tr>@endif
    </table>
</div>
@endsection
