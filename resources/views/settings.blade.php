@extends('layout')
@section('page','settings')
@section('title', __('ui.Settings'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Settings') }}</h1>
    <p>{{ __('ui.settings_sub') }}</p>
  </div>
</div>

@if($errors->any())<div class="note">{{ $errors->first() }}</div>@endif

<div class="tworow">
  <form method="post" action="{{ route('settings.profile') }}" class="card" style="margin:0">
    <h2 class="sect" style="margin-top:0">{{ __('ui.Profile') }}</h2>
    @csrf @method('PUT')
    <label style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.Name') }}</label>
    <input name="name" value="{{ old('name', $user->name) }}" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:13.5px;margin-bottom:14px">
    <label style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.E-mail') }}</label>
    <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:13.5px;margin-bottom:16px">
    <button type="submit" class="yes">{{ __('ui.Save') }}</button>
  </form>

  <form method="post" action="{{ route('settings.password') }}" class="card" style="margin:0">
    <h2 class="sect" style="margin-top:0">{{ __('ui.Change password') }}</h2>
    @csrf @method('PUT')
    <label style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.Current password') }}</label>
    <input type="password" name="current_password" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:13.5px;margin-bottom:14px">
    <label style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.New password') }}</label>
    <input type="password" name="password" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:13.5px;margin-bottom:14px">
    <label style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.Confirm password') }}</label>
    <input type="password" name="password_confirmation" required style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:13.5px;margin-bottom:16px">
    <button type="submit" class="yes">{{ __('ui.Update password') }}</button>
  </form>
</div>
@endsection
