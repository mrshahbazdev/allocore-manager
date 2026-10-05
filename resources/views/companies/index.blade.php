@extends('layouts.app')
@section('content')
<h1>Companies</h1>
<div class="card">
    <form method="GET" action="{{ route('companies.index') }}" style="margin-bottom:.75rem">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search name, ID or industry…" style="max-width:22rem">
        <select name="source_id">
            <option value="">All sources</option>
            @foreach ($sources as $src)
                <option value="{{ $src->id }}" @selected((string) $sourceId === (string) $src->id)>{{ $src->name }}</option>
            @endforeach
        </select>
        <button>Filter</button>
        <a href="{{ route('companies.index') }}">clear</a>
    </form>
    <table>
        <tr><th>Name</th><th>Source</th><th>Industry</th><th>Maturity</th><th>Situation</th><th>Signals</th><th>Recs</th><th>Needs</th><th>Last signal</th></tr>
        @foreach ($companies as $c)
        <tr>
            <td><a href="{{ route('companies.show', $c) }}">{{ $c->name ?? $c->external_id }}</a></td>
            <td>{{ $c->source?->name }}</td>
            <td>{{ $c->industry ?? '—' }}</td>
            <td>{{ $c->maturity ?? '—' }}</td>
            <td class="muted">{{ implode(', ', $c->situation ?? []) }}</td>
            <td>{{ $c->signals_count }}</td>
            <td>{{ $c->recommendations_count }}</td>
            <td>
                @if ($c->pending_recs_count)<span class="badge b-pending">{{ $c->pending_recs_count }} decision{{ $c->pending_recs_count > 1 ? 's' : '' }}</span>@endif
                @if ($c->unmeasured_count)<span class="badge b-partial">{{ $c->unmeasured_count }} unmeasured</span>@endif
            </td>
            <td>
                @if ($c->signals_max_occurred_at)
                    {{ \Illuminate\Support\Carbon::parse($c->signals_max_occurred_at)->diffForHumans(short: true) }}
                    @if (\Illuminate\Support\Carbon::parse($c->signals_max_occurred_at)->lt(now()->subDays(30)))
                        <span class="badge b-partial">quiet</span>
                    @endif
                @else
                    <span class="muted">never</span> <span class="badge b-partial">quiet</span>
                @endif
            </td>
        </tr>
        @endforeach
        @if ($companies->isEmpty())<tr><td colspan="9" class="muted">No companies yet.</td></tr>@endif
    </table>
    {{ $companies->links() }}
</div>
@endsection
