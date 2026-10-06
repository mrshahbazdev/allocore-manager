@extends('auth')
@section('title', __('ui.Create account'))
@section('form')
<form class="card" method="post" action="/register">
  <div class="eyebrow">{{ __('ui.decision intelligence') }}</div>
  <h2>{{ __('ui.Create account') }}</h2>
  <div class="sub">{{ __('ui.register_sub') }}</div>
  @csrf
  <label for="name">{{ __('ui.Name') }}</label>
  <input id="name" type="text" name="name" value="{{ old('name') }}" class="{{ $errors->has('name') ? 'inv' : '' }}" required autofocus>
  @error('name')<div class="fld-err">{{ $message }}</div>@enderror
  <label for="email">{{ __('ui.E-mail') }}</label>
  <input id="email" type="email" name="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'inv' : '' }}" required>
  @error('email')<div class="fld-err">{{ $message }}</div>@enderror
  <label for="company_key">{{ __('ui.Company') }} <span style="text-transform:none;font-weight:400">({{ __('ui.optional') }})</span></label>
  <input id="company_key" type="text" name="company_key" value="{{ old('company_key') }}">
  <label for="password">{{ __('ui.Password') }}</label>
  <input id="password" type="password" name="password" class="{{ $errors->has('password') ? 'inv' : '' }}" required>
  @error('password')<div class="fld-err">{{ $message }}</div>@enderror
  <label for="password_confirmation">{{ __('ui.Confirm password') }}</label>
  <input id="password_confirmation" type="password" name="password_confirmation" required>
  <button type="submit">{{ __('ui.Create account') }}</button>
  <div class="alt">
    <span>{{ __('ui.Already have an account?') }} <a href="{{ route('login') }}">{{ __('ui.Sign in') }}</a></span>
  </div>
</form>
@endsection
