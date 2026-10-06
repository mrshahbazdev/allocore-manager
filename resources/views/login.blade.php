<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#14181d">
<title>Allocore Manager — {{ __('ui.Sign in') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:'Sora',ui-sans-serif,system-ui,sans-serif;background:#14181d;display:grid;place-items:center;min-height:100vh;-webkit-font-smoothing:antialiased}
  .frame{width:100%;max-width:380px;padding:20px}
  .brandline{display:flex;align-items:center;gap:10px;color:#fff;font-size:13px;font-weight:700;letter-spacing:.08em;margin-bottom:22px}
  .brandline i{width:8px;height:8px;border-radius:50%;background:#e8890c}
  .card{background:#fff;border-radius:14px;padding:30px 28px;box-shadow:0 16px 48px rgba(0,0,0,.35)}
  .lang{display:flex;justify-content:flex-end;margin-bottom:6px}
  .lang a{color:#e8890c;font-weight:700;text-decoration:none;font-size:11px;letter-spacing:.08em}
  h1{font-size:19px;margin:0 0 4px;letter-spacing:-.01em;color:#1a1d21}
  .sub{color:#6b7280;font-size:12.5px;margin-bottom:22px;line-height:1.5}
  label{display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:#9aa3ad;font-weight:600;margin-bottom:6px}
  input{width:100%;padding:11px 12px;border:1px solid #dde2e8;border-radius:8px;margin-bottom:16px;font-size:14px;font-family:inherit;color:#1a1d21;transition:border-color .15s,box-shadow .15s}
  input:focus{outline:none;border-color:#e8890c;box-shadow:0 0 0 3px #e8890c22}
  button{width:100%;padding:11px;background:#1a1d21;color:#fff;border:0;border-radius:8px;font-weight:600;cursor:pointer;font-size:13.5px;font-family:inherit;letter-spacing:.02em}
  button:hover{background:#2b3138}
  .err{color:#c24134;font-size:12.5px;margin-bottom:14px}
  .foot{text-align:center;color:#5b6470;font-size:11px;margin-top:20px;letter-spacing:.03em}
</style>
</head>
<body>
<div class="frame">
  <div class="brandline"><i></i>ALLOCORE MANAGER</div>
  <form class="card" method="post" action="/login">
    <div class="lang"><a href="{{ route('lang', app()->getLocale() === 'de' ? 'en' : 'de') }}">{{ app()->getLocale() === 'de' ? 'EN' : 'DE' }}</a></div>
    <h1>{{ __('ui.Sign in') }}</h1>
    <div class="sub">{{ __('ui.login_sub') }}</div>
    @csrf
    @error('email')<div class="err">{{ __('ui.Wrong credentials.') }}</div>@enderror
    <label for="email">{{ __('ui.E-mail') }}</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
    <label for="password">{{ __('ui.Password') }}</label>
    <input id="password" type="password" name="password" required>
    <button type="submit">{{ __('ui.sign_in') }}</button>
  </form>
  <div class="foot">DISAVO ecosystem</div>
</div>
</body>
</html>
