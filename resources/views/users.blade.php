@extends('layout')
@section('page','users')
@section('title', __('ui.Users'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Users') }}</h1>
    <p>{{ __('ui.users_sub') }}</p>
  </div>
</div>

@if($errors->any())<div class="note">{{ $errors->first() }}</div>@endif
@if(session('status'))<div class="note">{{ session('status') }}</div>@endif

<div class="card" style="margin-bottom:16px">
  <div class="k" style="font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:10px">{{ __('ui.New user') }}</div>
  <form method="post" action="{{ route('users.store') }}" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px;align-items:end">
    @csrf
    <input name="name" required placeholder="{{ __('ui.Name') }}" style="padding:7px 9px;border:1px solid var(--line);border-radius:7px;font-family:inherit;font-size:12px">
    <input name="email" type="email" required placeholder="{{ __('ui.E-mail') }}" style="padding:7px 9px;border:1px solid var(--line);border-radius:7px;font-family:inherit;font-size:12px">
    <input name="password" type="text" required minlength="8" placeholder="{{ __('ui.Password') }}" style="padding:7px 9px;border:1px solid var(--line);border-radius:7px;font-family:inherit;font-size:12px">
    <select name="role" style="padding:7px 9px;border:1px solid var(--line);border-radius:7px;font-family:inherit;font-size:12px;background:#fff">
      @foreach($roles as $r)
        <option value="{{ $r }}">{{ __('ui.'.$r) }}</option>
      @endforeach
    </select>
    <input name="company_key" placeholder="{{ __('ui.Company') }}" style="padding:7px 9px;border:1px solid var(--line);border-radius:7px;font-family:inherit;font-size:12px">
    <div><button type="submit" class="yes">{{ __('ui.Create') }}</button></div>
  </form>
</div>

<table>
  <thead><tr>
    <th>{{ __('ui.Name') }}</th><th>{{ __('ui.E-mail') }}</th><th>{{ __('ui.Role') }}</th><th>{{ __('ui.Company') }}</th><th></th>
  </tr></thead>
  <tbody>
  @foreach($users as $u)
    <tr>
      <td><b>{{ $u->name }}</b></td>
      <td style="color:var(--muted)">{{ $u->email }}</td>
      <td>
        <select name="role" form="u{{ $u->id }}" style="padding:7px 9px;border:1px solid var(--line);border-radius:7px;font-family:inherit;font-size:12px;background:#fff">
          @foreach($roles as $r)
            <option value="{{ $r }}" @selected($u->role === $r)>{{ __('ui.'.$r) }}</option>
          @endforeach
        </select>
      </td>
      <td><input name="company_key" form="u{{ $u->id }}" value="{{ $u->company_key }}" style="padding:7px 9px;border:1px solid var(--line);border-radius:7px;font-family:inherit;font-size:12px;width:150px"></td>
      <td style="text-align:right">
        <form id="u{{ $u->id }}" method="post" action="{{ route('users.update', $u) }}">@csrf @method('PUT')<button type="submit" class="yes">{{ __('ui.Save') }}</button></form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>
@endsection
