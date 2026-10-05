@extends('layouts.app')
@section('content')
<h1>Edit {{ $source->name }}</h1>
<div class="card">
    <p class="muted">Ingest token: <code>{{ $source->ingest_token }}</code></p>
    <form method="POST" action="{{ route('sources.update', $source) }}">
        @csrf @method('PATCH')
        <div class="grid-2">
            <div><label>Name</label><input name="name" value="{{ $source->name }}" required></div>
            <div>
                <label>Type</label>
                <select name="type">
                    @foreach (['saas_platform' => 'SaaS platform', 'company' => 'Company', 'system' => 'System (CRM/ERP/finance)'] as $v => $l)
                        <option value="{{ $v }}" @selected($source->type === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div><label><input type="checkbox" name="is_active" value="1" @checked($source->is_active) style="width:auto"> Active</label></div>
        </div>
        <div style="margin-top:1rem"><input type="submit" value="Save"></div>
    </form>
</div>
@endsection
