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

@if($errors->any())<div class="note">{{ $errors->first() }}</div>@endif
@if(session('status'))<div class="note">{{ session('status') }}</div>@endif

@if($companies->count())
  <table>
    <tr><th>{{ __('ui.Company') }}</th><th>{{ __('ui.Signals') }}</th><th>{{ __('ui.open') }}</th><th>{{ __('ui.decided') }}</th>@if($user->role === 'allocore')<th></th>@endif</tr>
    @foreach($companies as $c)
      <tr>
        <td><a href="{{ route('signals', ['company' => $c->company_key]) }}" style="color:var(--accent);text-decoration:none;font-weight:600">{{ $c->company_key }}</a></td>
        <td class="num">{{ $c->signals }}</td>
        <td class="num">{{ $c->open }}</td>
        <td class="num">{{ $c->done }}</td>
        @if($user->role === 'allocore')
        <td style="text-align:right;white-space:nowrap">
          <form method="post" action="{{ route('companies.rename') }}" style="display:flex;gap:6px;justify-content:flex-end">
            @csrf
            <input type="hidden" name="from" value="{{ $c->company_key }}">
            <input name="to" required placeholder="{{ __('ui.New name') }}" style="padding:6px 8px;border:1px solid var(--line);border-radius:7px;font-family:inherit;font-size:12px;width:140px">
            <button type="submit" class="yes">{{ __('ui.Rename') }}</button>
          </form>
        </td>
        @endif
      </tr>
    @endforeach
  </table>
  @if($user->role === 'allocore')
    <p style="margin-top:10px;font-size:11.5px;color:var(--faint)">{{ __('ui.company_rename_hint') }}</p>
  @endif
@else
  <div class="empty"><span class="mark">✓</span><div>{{ __('ui.No companies reporting yet.') }}</div></div>
@endif
@endsection
