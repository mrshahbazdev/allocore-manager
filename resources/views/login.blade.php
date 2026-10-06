@extends('auth')
@section('title', __('ui.Sign in'))
@section('form')
<form class="card" method="post" action="/login">
  <div class="eyebrow">{{ __('ui.decision intelligence') }}</div>
  <h2>{{ __('ui.Sign in') }}</h2>
  <div class="sub">{{ __('ui.login_sub') }}</div>
  @csrf
  @if(session('status'))<div class="okbox">{{ session('status') }}</div>@endif
  @error('email')<div class="err">{{ __('ui.Wrong credentials.') }}</div>@enderror
  <label for="email">{{ __('ui.E-mail') }}</label>
  <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
  <label for="password">{{ __('ui.Password') }}</label>
  <input id="password" type="password" name="password" required>
  <button type="submit">{{ __('ui.sign_in') }}</button>
  <div class="alt">
    <a href="{{ route('password.request') }}">{{ __('ui.Forgot your password?') }}</a>
    <span>{{ __('ui.No account yet?') }} <a href="{{ route('register') }}">{{ __('ui.Create one') }}</a></span>
  </div>
</form>
@endsection
