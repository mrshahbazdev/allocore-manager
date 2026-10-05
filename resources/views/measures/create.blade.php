@extends('layouts.app')
@section('content')
<h1>New action measure</h1>
<div class="card">
    <form method="POST" action="{{ route('measures.store') }}">
        @csrf
        <div class="grid-2">
            <div><label>Key (e.g. quarterly_access_reviews)</label><input name="key" required></div>
            <div><label>Name</label><input name="name" required></div>
        </div>
        <label>Description</label><textarea name="description" rows="3"></textarea>
        <label>Addresses challenges (comma separated)</label>
        <input name="addresses_challenges" placeholder="missing_access_review, stale_permissions">
        <div style="margin-top:1rem"><input type="submit" value="Add to catalog"></div>
    </form>
</div>
@endsection
