<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#14181d">
<title>Allocore Manager — {{ __('ui.Sign in') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:'Sora',ui-sans-serif,system-ui,sans-serif;-webkit-font-smoothing:antialiased}
  .split{display:grid;grid-template-columns:1fr 1fr;min-height:100vh}
  .hero{background:#14181d;color:#c9d2db;padding:56px;display:flex;flex-direction:column;justify-content:space-between}
  .hero .brandline{display:flex;align-items:center;gap:10px;color:#fff;font-size:14px;font-weight:700;letter-spacing:.06em}
  .hero .brandline i{width:8px;height:8px;border-radius:50%;background:#e8890c}
  .hero .brandline .tag{color:#7d8896;font-weight:500;font-size:10.5px;letter-spacing:.05em;text-transform:uppercase}
  .hero h1{color:#fff;font-size:34px;letter-spacing:-.02em;line-height:1.15;margin:0 0 16px;font-weight:700;max-width:14ch}
  .hero h1 .amber{color:#e8890c}
  .hero .lede{color:#8b96a3;font-size:14px;line-height:1.65;max-width:38ch;margin:0}
  .hero .loop{margin-top:34px;display:flex;flex-direction:column;gap:0;max-width:340px}
  .hero .loop .step{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid #2c343e;font-size:12.5px;color:#aab4c0}
  .hero .loop .step:last-child{border-bottom:none}
  .hero .loop .step .n{font-family:'JetBrains Mono',monospace;font-size:10px;color:#e8890c;width:20px;flex-shrink:0}
  .hero .foot{color:#5b6572;font-size:11px;letter-spacing:.05em}
  .pane{background:#f4f5f6;display:grid;place-items:center;padding:24px}
  .frame{width:100%;max-width:400px}
  .lang{display:flex;justify-content:flex-end;margin-bottom:14px}
  .lang a{color:#e8890c;font-weight:700;text-decoration:none;font-size:11px;letter-spacing:.08em}
  .card{background:#fff;border:1px solid #e5e8ec;border-radius:14px;padding:32px 30px;box-shadow:0 2px 16px rgba(20,24,29,.06)}
  .card .eyebrow{font-size:10px;text-transform:uppercase;letter-spacing:.12em;color:#9aa3ad;font-weight:700;margin-bottom:8px}
  .card h2{font-size:20px;margin:0 0 6px;letter-spacing:-.02em;color:#1a1d21;font-weight:700}
  .card .sub{color:#6b7280;font-size:12.5px;margin-bottom:24px;line-height:1.5}
  label{display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:#9aa3ad;font-weight:600;margin-bottom:6px}
  input{width:100%;padding:11px 12px;border:1px solid #dde2e8;border-radius:8px;margin-bottom:16px;font-size:14px;font-family:inherit;color:#1a1d21;background:#fff;transition:border-color .15s,box-shadow .15s}
  input:focus{outline:none;border-color:#e8890c;box-shadow:0 0 0 3px #e8890c22}
  button{width:100%;padding:12px;background:#1a1d21;color:#fff;border:0;border-radius:8px;font-weight:600;cursor:pointer;font-size:13.5px;font-family:inherit;letter-spacing:.02em;transition:background .15s}
  button:hover{background:#2b3138}
  .err{background:#fbeae8;border:1px solid #f0c5c1;color:#c24134;font-size:12.5px;padding:10px 12px;border-radius:8px;margin-bottom:16px}
  .mfoot{text-align:center;color:#9aa3ad;font-size:11px;margin-top:18px;letter-spacing:.03em}
  @media (max-width:880px){
    .split{grid-template-columns:1fr}
    .hero{padding:36px 28px;min-height:0}
    .hero .loop,.hero .lede{display:none}
    .hero h1{font-size:24px;margin-bottom:0}
    .hero .foot{display:none}
  }
</style>
</head>
<body>
<div class="split">
  <aside class="hero">
    <div class="brandline"><i></i>ALLOCORE <span class="tag">{{ __('ui.Manager') }}</span></div>
    <div>
      <h1>{{ __('ui.hero_title_a') }} <span class="amber">{{ __('ui.hero_title_b') }}</span></h1>
      <p class="lede">{{ __('ui.hero_lede') }}</p>
      <div class="loop">
        <div class="step"><span class="n">01</span>{{ __('ui.loop_1') }}</div>
        <div class="step"><span class="n">02</span>{{ __('ui.loop_2') }}</div>
        <div class="step"><span class="n">03</span>{{ __('ui.loop_3') }}</div>
        <div class="step"><span class="n">04</span>{{ __('ui.loop_4') }}</div>
      </div>
    </div>
    <div class="foot">DISAVO ecosystem</div>
  </aside>
  <main class="pane">
    <div class="frame">
      <div class="lang"><a href="{{ route('lang', app()->getLocale() === 'de' ? 'en' : 'de') }}">{{ app()->getLocale() === 'de' ? 'EN' : 'DE' }}</a></div>
      <form class="card" method="post" action="/login">
        <div class="eyebrow">{{ __('ui.decision intelligence') }}</div>
        <h2>{{ __('ui.Sign in') }}</h2>
        <div class="sub">{{ __('ui.login_sub') }}</div>
        @csrf
        @error('email')<div class="err">{{ __('ui.Wrong credentials.') }}</div>@enderror
        <label for="email">{{ __('ui.E-mail') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        <label for="password">{{ __('ui.Password') }}</label>
        <input id="password" type="password" name="password" required>
        <button type="submit">{{ __('ui.sign_in') }}</button>
      </form>
      <div class="mfoot">{{ __('ui.mfoot') }}</div>
    </div>
  </main>
</div>
</body>
</html>
