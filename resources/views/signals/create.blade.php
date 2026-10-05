@extends('layouts.app')
@section('content')
<h1>Record a signal</h1>
<div class="card">
    <form method="POST" action="{{ route('signals.store') }}">
        @csrf
        <label>Source platform</label>
        <select name="source_id">
            @foreach ($sources as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->key }})</option>@endforeach
        </select>
        <div class="grid-2">
            <div>
                <label>Signal type</label>
                <input name="type" required placeholder="risk.detected">
            </div>
            <div>
                <label>Challenge key</label>
                <input name="challenge_key" placeholder="missing_access_review">
            </div>
            <div>
                <label>User id (on platform)</label>
                <input name="user_id">
            </div>
            <div>
                <label>Occurred at</label>
                <input name="occurred_at" type="datetime-local">
            </div>
        </div>
        <h2 style="margin-top:1rem">Company context</h2>
        <div class="grid-2">
            <div>
                <label>Company id (on platform)</label>
                <input name="company_external_id">
            </div>
            <div>
                <label>Name</label>
                <input name="company_name">
            </div>
            <div>
                <label>Industry</label>
                <input name="company_industry">
            </div>
            <div>
                <label>Size</label>
                <input name="company_size" type="number" min="0">
            </div>
            <div>
                <label>Maturity</label>
                <select name="company_maturity">
                    <option value="">—</option>
                    <option>early</option><option>growing</option><option>established</option>
                </select>
            </div>
            <div>
                <label>Situation tags (comma separated)</label>
                <input name="company_situation" placeholder="compliance_backlog, no_it_team">
            </div>
        </div>
        <div style="margin-top:1rem"><input type="submit" value="Record signal"></div>
    </form>
</div>
@endsection
