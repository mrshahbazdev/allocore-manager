@extends('layouts.app')
@section('content')
<h1>Recommendations</h1>
<div class="card">
    <p>
        Filter:
        @foreach (['pending', 'accepted', 'implemented', 'dismissed'] as $s)
            <a href="{{ route('recommendations.index', ['status' => $s]) }}" class="{{ $status === $s ? 'badge b-'.$s : 'muted' }}">{{ $s }}</a>
        @endforeach
        · <a href="{{ route('recommendations.index') }}" class="{{ $status ? 'muted' : '' }}">all</a>
    </p>
    <form method="POST" action="{{ route('recommendations.bulk') }}">@csrf
    <p>
        <select name="status">
            <option value="accepted">Accept</option>
            <option value="implemented">Mark implemented</option>
            <option value="dismissed">Dismiss</option>
        </select>
        <button>Apply to selected pending</button>
    </p>
    <table>
        <tr><th></th><th>Company</th><th>Challenge</th><th>Recommended</th><th>Conf</th><th>Status</th><th>Outcome</th><th>Created</th></tr>
        @foreach ($recommendations as $r)
        <tr>
            <td>@if ($r->status === 'pending')<input type="checkbox" name="ids[]" value="{{ $r->id }}">@endif</td>
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
        </tr>
        @endforeach
        @if ($recommendations->isEmpty())<tr><td colspan="8" class="muted">None{{ $status ? " with status {$status}" : '' }}.</td></tr>@endif
    </table>
    </form>
</div>
@endsection
