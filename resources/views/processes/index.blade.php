@extends('layouts.app')
@section('content')
<h1>Automation tracker</h1>
<p class="muted" style="margin-bottom:1rem">Every process evolves: manual → assisted → semi-automated → automated. The long-term goal is autonomous decision intelligence.</p>
<div class="card">
    <h2>Register a process</h2>
    <form method="POST" action="{{ route('processes.store') }}" class="grid-2">
        @csrf
        <div><label>Name</label><input name="name" required></div>
        <div>
            <label>Scope</label>
            <select name="scope"><option>ecosystem</option><option>platform</option><option>company</option></select>
        </div>
        <div style="grid-column:1/-1"><label>Description</label><input name="description"></div>
        <div><input type="submit" value="Register (as manual)"></div>
    </form>
</div>
<div class="card">
    <h2>Processes</h2>
    <table>
        <tr><th>Process</th><th>Scope</th><th>Stage</th><th>Progress</th><th>History</th><th>Advance</th></tr>
        @foreach ($processes as $p)
        <tr>
            <td>{{ $p->name }}<div class="muted">{{ $p->description }}</div></td>
            <td>{{ $p->scope }}</td>
            <td><span class="badge b-accepted">{{ str_replace('_', ' ', $p->automation_stage) }}</span></td>
            <td>
                @php $idx = $p->stageIndex(); @endphp
                @foreach ($stages as $i => $s)
                    <span style="display:inline-block;width:2.2rem;height:.5rem;border-radius:.25rem;margin-right:.2rem;background:{{ $i <= $idx ? '#166534' : '#e4e4e7' }}"></span>
                @endforeach
            </td>
            <td class="muted">{{ $p->assessments->count() }} change(s)</td>
            <td>
                <form class="inline" method="POST" action="{{ route('processes.advance', $p) }}">@csrf
                    <select name="stage" style="width:auto;display:inline">
                        @foreach ($stages as $s)<option value="{{ $s }}" @selected($s === $p->automation_stage)>{{ str_replace('_', ' ', $s) }}</option>@endforeach
                    </select>
                    <button>Set</button>
                </form>
            </td>
        </tr>
        @endforeach
        @if ($processes->isEmpty())<tr><td colspan="6" class="muted">No processes tracked yet.</td></tr>@endif
    </table>
</div>
@endsection
