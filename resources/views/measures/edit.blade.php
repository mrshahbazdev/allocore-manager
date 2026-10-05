@extends('layouts.app')
@section('content')
<h1>Edit measure — <code>{{ $measure->key }}</code></h1>
<div class="card">
    <form method="POST" action="{{ route('measures.update', $measure) }}">
        @csrf @method('PATCH')
        <div><label>Name</label><input name="name" value="{{ old('name', $measure->name) }}" required></div>
        <label>Description</label><textarea name="description" rows="3">{{ old('description', $measure->description) }}</textarea>
        <label>Addresses challenges (comma separated)</label>
        <input name="addresses_challenges" value="{{ old('addresses_challenges', implode(', ', $measure->addresses_challenges ?? [])) }}">
        <div style="margin-top:.5rem">
            <label><input type="checkbox" name="is_active" value="1" {{ $measure->is_active ? 'checked' : '' }}> Active</label>
        </div>
        <div style="margin-top:1rem"><input type="submit" value="Save"></div>
    </form>
</div>
@endsection
