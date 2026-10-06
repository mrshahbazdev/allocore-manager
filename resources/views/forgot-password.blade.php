@extends('auth')
@section('title', __('ui.Forgot password'))
@section('form')
<form class="card" method="post" action="/forgot-password">
  <div class="eyebrow">{{ __('ui.decision intelligence') }}</div>
  <h2>{{ __('ui.Forgot password') }}</h2>
  <div class="sub">{{ __('ui.forgot_sub') }}</div>
  @csrf
  @if(session('status'))<div class="okbox">{{ __(session('status')) }}</div>@endif
  <label for="email">{{ __('ui.E-mail') }}</label>
  <input id="email" type="email" name="email" value="{{ old('email') }}" class="{{ $errors->has('email') ? 'inv' : '' }}" required autofocus>
  @error('email')<div class="fld-err">{{ __('ui.reset_link_err') }}</div>@enderror
  <button type="submit">{{ __('ui.Send reset link') }}</button>
  <div class="alt">
    <a href="{{ route('login') }}">← {{ __('ui.Back to sign in') }}</a>
  </div>
</form>
@endsection
