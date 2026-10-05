@extends('layouts.app')
@section('content')
<h1>Measure outcome</h1>
<div class="card">
    <p><strong>{{ $recommendation->actionMeasure?->name }}</strong> for
        <a href="{{ route('companies.show', $recommendation->company) }}">{{ $recommendation->company?->name }}</a>
        — challenge <code>{{ $recommendation->challenge_key }}</code></p>
    <p class="muted">Recording the result teaches the system: patterns for this challenge × measure update, and future recommendations for similar companies get better.</p>

    <form method="POST" action="{{ route('recommendations.outcome', $recommendation) }}" style="margin-top:1rem">
        @csrf
        <label><strong>Result</strong></label><br>
        <label><input type="radio" name="result" value="success" required> Success — the problem was solved</label><br>
        <label><input type="radio" name="result" value="partial"> Partial — some improvement, not fully solved</label><br>
        <label><input type="radio" name="result" value="failure"> Failed — did not work</label>

        <div style="margin-top:1rem">
            <label><strong>Why did it fail? (required if failed)</strong></label><br>
            <input name="failure_reason" style="width:24rem" placeholder="e.g. no_budget, no_time, tool_rejected, scope_too_big">
            <p class="muted">Short snake_case key — the system groups failures by reason to learn where each measure breaks down.</p>
        </div>

        <button style="margin-top:1rem">Record outcome</button>
    </form>
</div>
@endsection
