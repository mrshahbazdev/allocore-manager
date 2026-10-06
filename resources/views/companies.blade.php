@extends('layout')
@section('page', 'companies')
@section('title', __('ui.Companies'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Companies') }}</h1>
    <p>{{ __('ui.companies_sub') }}</p>
  </div>
</div>

@if($companies->count())
  <table>
    <tr><th>{{ __('ui.Company') }}</th><th>{{ __('ui.Signals') }}</th><th>{{ __('ui.open') }}</th><th>{{ __('ui.decided') }}</th></tr>
    @foreach($companies as $c)
      <tr>
        <td><a href="{{ route('signals', ['company' => $c->company_key]) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $c->company_key }}</a></td>
        <td class="num">{{ $c->signals }}</td>
        <td class="num">{{ $c->open }}</td>
        <td class="num">{{ $c->done }}</td>
      </tr>
    @endforeach
  </table>
@else
  <div class="empty"><span class="mark">✓</span><div>{{ __('ui.No companies reporting yet.') }}</div></div>
@endif
@endsection
