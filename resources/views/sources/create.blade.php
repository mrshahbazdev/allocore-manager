@extends('layouts.app')
@section('content')
<h1>Register a data source</h1>
<div class="card">
    <form method="POST" action="{{ route('sources.store') }}">
        @csrf
        <div class="grid-2">
            <div><label>Key (e.g. compliancetermine-de)</label><input name="key" required></div>
            <div><label>Name</label><input name="name" required></div>
            <div>
                <label>Type</label>
                <select name="type">
                    <option value="saas_platform">SaaS platform</option>
                    <option value="company">Company</option>
                    <option value="system">System (CRM/ERP/finance)</option>
                </select>
            </div>
            <div><label>&nbsp;</label><label><input type="checkbox" name="is_active" value="1" checked style="width:auto"> Active</label></div>
        </div>
        <div style="margin-top:1rem"><input type="submit" value="Register"></div>
    </form>
</div>
@endsection
