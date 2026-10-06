@extends('auth')
@section('title', __('ui.Reset password'))
@section('form')
<form class="card" method="post" action="/reset-password">
  <div class="eyebrow">{{ __('ui.decision intelligence') }}</div>
  <h2>{{ __('ui.Reset password') }}</h2>
  <div class="sub">{{ __('ui.reset_sub') }}</div>
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <label for="email">{{ __('ui.E-mail') }}</label>
  <input id="email" type="email" name="email" value="{{ old('email', $email ?? '') }}" class="{{ $errors->has('email') ? 'inv' : '' }}" required autofocus>
  @error('email')<div class="fld-err">{{ $message }}</div>@enderror
  <label for="password">{{ __('ui.New password') }}</label>
  <input id="password" type="password" name="password" class="{{ $errors->has('password') ? 'inv' : '' }}" required>
  @error('password')<div class="fld-err">{{ $message }}</div>@enderror
  <label for="password_confirmation">{{ __('ui.Confirm password') }}</label>
  <input id="password_confirmation" type="password" name="password_confirmation" required>
  @error('token')<div class="fld-err">{{ __('ui.reset_link_err') }}</div>@enderror
  <button type="submit">{{ __('ui.Reset password') }}</button>
  <div class="alt">
    <a href="{{ route('login') }}">← {{ __('ui.Back to sign in') }}</a>
  </div>
</form>
@endsection
