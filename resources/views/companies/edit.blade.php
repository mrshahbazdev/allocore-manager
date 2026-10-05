@extends('layouts.app')
@section('content')
<h1>Edit company</h1>
<div class="card">
    <form method="POST" action="{{ route('companies.update', $company) }}">
        @csrf @method('PUT')
        <p><label>Name<br><input name="name" value="{{ old('name', $company->name) }}" style="width:24rem"></label></p>
        <p><label>Industry<br><input name="industry" value="{{ old('industry', $company->industry) }}" style="width:24rem"></label></p>
        <p><label>Maturity<br><input name="maturity" value="{{ old('maturity', $company->maturity) }}" placeholder="early / growing / mature" style="width:24rem"></label></p>
        <p><label>Situation tags (comma separated)<br>
            <input name="situation" value="{{ old('situation', implode(', ', $company->situation ?? [])) }}" style="width:24rem" placeholder="no_it_team, compliance_backlog">
        </label></p>
        <p class="muted">Maturity + situation decide which cohort this company belongs to — they directly change which recommendations it gets.</p>
        <button>Save</button>
    </form>
</div>
@endsection
