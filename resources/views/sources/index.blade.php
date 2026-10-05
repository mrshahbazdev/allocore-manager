@extends('layouts.app')
@section('content')
<h1>Data providers</h1>
<div class="card">
    <a href="{{ route('sources.create') }}"><button>Register source</button></a>
    <table style="margin-top:.75rem">
        <tr><th>Key</th><th>Name</th><th>Type</th><th>Companies</th><th>Signals</th><th>Last signal</th><th>Feed</th><th>Status</th><th></th></tr>
        @foreach ($sources as $s)
        <tr>
            <td><code>{{ $s->key }}</code></td>
            <td>{{ $s->name }}</td>
            <td>{{ $s->type }}</td>
            <td>{{ $s->companies_count }}</td>
            <td>{{ $s->signals_count }}</td>
            <td class="muted">{{ $s->signals_max_occurred_at ? \Illuminate\Support\Carbon::parse($s->signals_max_occurred_at)->diffForHumans() : 'never' }}</td>
            <td>
                @php $days = $s->signals_max_occurred_at ? \Illuminate\Support\Carbon::parse($s->signals_max_occurred_at)->diffInDays(now()) : null; @endphp
                @if (is_null($days))
                    <span class="badge b-dismissed">no data</span>
                @elseif ($days <= 7)
                    <span class="badge b-success">healthy</span>
                @elseif ($days <= 30)
                    <span class="badge b-pending">quiet</span>
                @else
                    <span class="badge b-failure">stale</span>
                @endif
            </td>
            <td><span class="badge b-{{ $s->is_active ? 'success' : 'dismissed' }}">{{ $s->is_active ? 'active' : 'inactive' }}</span></td>
            <td><a href="{{ route('sources.edit', $s) }}">Edit</a> · <a href="{{ route('sources.import', $s) }}">Import</a> · <a href="{{ route('intelligence.platform', $s) }}">Intelligence</a></td>
        </tr>
        @endforeach
    </table>
</div>
@endsection
