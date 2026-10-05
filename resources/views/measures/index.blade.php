@extends('layouts.app')
@section('content')
<h1>Action measure catalog</h1>
@if ($failing->isNotEmpty())
<div class="card" style="border:1px solid #fecaca">
    <h2>Failing measures — what doesn't work</h2>
    <p class="muted">At least 3 attempts and under 50% success. Worth revising or retiring.</p>
    <table>
        <tr><th>Measure</th><th>Attempts</th><th>Success</th></tr>
        @foreach ($failing as $m)
        <tr>
            <td><a href="{{ route('measures.show', $m) }}">{{ $m->name }}</a></td>
            <td>{{ $m->aggregate_attempts }}</td>
            <td><span class="badge b-failure">{{ $m->aggregate_success_rate }}%</span></td>
        </tr>
        @endforeach
    </table>
</div>
@endif

<div class="card">
    <a href="{{ route('measures.create') }}"><button>New measure</button></a>
    <table style="margin-top:.75rem">
        <tr><th>Key</th><th>Name</th><th>Addresses</th><th>Recs</th><th>Status</th><th></th></tr>
        @foreach ($measures as $m)
        <tr>
            <td><code>{{ $m->key }}</code></td>
            <td><a href="{{ route('measures.show', $m) }}">{{ $m->name }}</a></td>
            <td class="muted">{{ implode(', ', $m->addresses_challenges ?? []) }}
                @if (empty(array_intersect($m->addresses_challenges ?? [], $signalled->all())))<span class="badge b-partial">no signals</span>@endif</td>
            <td>{{ $m->recommendations_count }}</td>
            <td><span class="badge b-{{ $m->is_active ? 'success' : 'dismissed' }}">{{ $m->is_active ? 'active' : 'inactive' }}</span></td>
            <td style="white-space:nowrap">
                <a href="{{ route('measures.edit', $m) }}">Edit</a>
                <form class="inline" method="POST" action="{{ route('measures.update', $m) }}">@csrf @method('PATCH')
                    <input type="hidden" name="is_active" value="{{ $m->is_active ? 0 : 1 }}">
                    <button class="secondary">{{ $m->is_active ? 'Deactivate' : 'Activate' }}</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection
