@extends('layouts.app')
@section('content')
<h1>Import signals — {{ $source->name }}</h1>
<p class="muted" style="margin-bottom:1rem">
    Paste one JSON object per line. Fields: <code>type</code> (required), <code>challenge_key</code>,
    <code>user_id</code>, <code>occurred_at</code>, <code>company</code> = {"external_id": "...", "name": "...", "industry": "...", "maturity": "...", "situation": ["..."]}
    <br><br>
    Loop-closing types: <code>action.implemented</code> and <code>outcome.measured</code> carry
    <code>measure_key</code> (and <code>result</code>: success|partial|failure, <code>failure_reason</code>) —
    they update the matching recommendation and fold the outcome back into the learning patterns automatically.
</p>
<div class="card">
    <form method="POST" action="{{ route('sources.import.store', $source) }}">
        @csrf
        <label>JSONL</label>
        <textarea name="lines" rows="12" placeholder='{"type":"risk.detected","challenge_key":"missing_access_review","company":{"external_id":"acme-1","name":"Acme"}}'></textarea>
        <div style="margin-top:1rem"><input type="submit" value="Import"></div>
    </form>
</div>
@endsection
