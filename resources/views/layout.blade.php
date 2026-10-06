<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#14181d">
<title>Allocore Manager — @yield('title')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1a1d21; --muted:#6b7280; --faint:#9aa3ad;
    --canvas:#f4f5f6; --surface:#ffffff; --line:#e5e8ec; --line2:#eef1f4;
    --amber:#e8890c; --amber-soft:#fdf1df;
    --critical:#c24134; --critical-soft:#fbeae8;
    --warning:#a16207; --warning-soft:#fdf4dc;
    --info:#b3640e; --info-soft:#fdefe0;
    --ok:#1e7d4f; --ok-soft:#e8f5ee;
    --dark:#14181d; --dark2:#1e242b; --dark-line:#2c343e;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:'Sora',ui-sans-serif,system-ui,sans-serif;background:var(--canvas);color:var(--ink);font-size:14px;-webkit-font-smoothing:antialiased}
  a{text-decoration:none;color:inherit}

  .shell{display:grid;grid-template-columns:228px 1fr;min-height:100vh}

  /* Sidebar */
  .side{background:var(--dark);color:#c9d2db;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;padding:20px 14px;z-index:30}
  .side .brand{display:flex;align-items:center;gap:10px;padding:4px 10px 18px;border-bottom:1px solid var(--dark-line);font-size:13.5px;font-weight:700;letter-spacing:.06em;color:#fff}
  .side .brand .dot{width:8px;height:8px;border-radius:50%;background:var(--amber)}
  .side .brand .tag{font-weight:500;color:#7d8896;font-size:10.5px;letter-spacing:.05em;text-transform:uppercase}
  .nav{margin-top:14px;display:flex;flex-direction:column;gap:2px;flex:1}
  .nav .sect{font-size:9.5px;text-transform:uppercase;letter-spacing:.14em;color:#5b6572;padding:14px 10px 6px;font-weight:700}
  .nav a{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;font-size:13px;font-weight:500;color:#aab4c0}
  .nav a svg{width:15px;height:15px;stroke:currentColor;flex-shrink:0}
  .nav a:hover{background:var(--dark2);color:#e3e8ee}
  .nav a.on{background:var(--dark2);color:#fff}
  .nav a.on svg{stroke:var(--amber)}
  .nav a .n{margin-left:auto;font-family:'JetBrains Mono',monospace;font-size:10px;background:#2a323b;border-radius:5px;padding:1px 6px;color:#9aa7b4}
  .side .foot{border-top:1px solid var(--dark-line);padding-top:14px}
  .side .who{padding:6px 10px}
  .side .who .uname{color:#e3e8ee;font-weight:600;font-size:13px}
  .side .who .role{display:inline-block;margin-top:4px;border:1px solid var(--dark-line);border-radius:6px;padding:2px 8px;font-size:9.5px;letter-spacing:.08em;text-transform:uppercase;color:#9aa7b4}
  .side .footrow{display:flex;justify-content:space-between;align-items:center;padding:8px 10px}
  .side .lang{color:var(--amber);font-weight:700;font-size:11px;letter-spacing:.08em}
  .side form{margin:0}
  .side .footrow button{background:transparent;border:1px solid var(--dark-line);color:#c9d2db;border-radius:6px;padding:4px 10px;cursor:pointer;font-size:11px;font-family:inherit}
  .side .footrow button:hover{background:var(--dark2)}

  /* Content */
  .content{min-width:0}
  .topbar{height:56px;background:var(--surface);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 28px;position:sticky;top:0;z-index:20}
  .topbar .crumb{font-size:10.5px;text-transform:uppercase;letter-spacing:.12em;color:var(--faint);font-weight:700}
  .topbar .crumb b{color:var(--muted);font-weight:600}
  .main{padding:28px;max-width:1120px}

  .pagehead{margin-bottom:22px;display:flex;justify-content:space-between;align-items:flex-end;gap:16px}
  .pagehead h1{font-size:22px;margin:0 0 4px;letter-spacing:-.02em;font-weight:700}
  .pagehead p{margin:0;color:var(--muted);font-size:12.5px}
  .note{background:var(--warning-soft);border:1px solid #f0dcae;border-radius:10px;padding:11px 14px;font-size:12px;color:var(--warning);margin-bottom:18px}

  .statgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px}
  .statcard{background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:16px 18px}
  .statcard .k{font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:8px}
  .statcard .v{font-family:'JetBrains Mono',monospace;font-size:26px;font-weight:700;letter-spacing:-.02em}
  .statcard .v.amber{color:var(--amber)}

  h2.sect{font-size:10.5px;text-transform:uppercase;letter-spacing:.12em;color:var(--faint);margin:26px 0 10px;font-weight:700;display:flex;justify-content:space-between;align-items:center}
  h2.sect a{color:var(--amber);text-transform:none;letter-spacing:.02em;font-size:12px;font-weight:600}
  .panel{background:var(--surface);border:1px solid var(--line);border-radius:12px;overflow:hidden}
  .panel .row{display:flex;justify-content:space-between;align-items:baseline;padding:12px 16px;border-bottom:1px solid var(--line2);font-size:12.5px}
  .panel .row:last-child{border-bottom:none}
  .panel .row b{font-family:'JetBrains Mono',monospace;font-weight:700;font-size:15px}
  .panel .row .k{color:var(--muted)}

  table{width:100%;border-collapse:separate;border-spacing:0;background:var(--surface);border:1px solid var(--line);border-radius:12px;overflow:hidden;font-size:12.5px}
  th,td{padding:10px 14px;border-bottom:1px solid var(--line2);text-align:left}
  th{background:#f8f9fa;color:var(--faint);font-size:10px;text-transform:uppercase;letter-spacing:.08em;font-weight:600}
  td.num{font-family:'JetBrains Mono',monospace;font-weight:500}
  tr:last-child td{border-bottom:none}
  .tworow{display:grid;grid-template-columns:1fr 1fr;gap:12px}

  .card{background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:18px 20px 16px;margin-bottom:12px;transition:box-shadow .15s,border-color .15s}
  .card:hover{border-color:#d5dae0;box-shadow:0 2px 8px rgba(20,24,29,.05)}
  .card .top{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:6px}
  .card .t{font-weight:700;font-size:15px;line-height:1.35;letter-spacing:-.01em}
  .card .d{font-size:13px;color:#3d4550;line-height:1.6;max-width:62ch}
  .sev{display:flex;align-items:center;gap:6px;flex-shrink:0;font-size:10.5px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:4px 10px;border-radius:6px}
  .sev i{width:6px;height:6px;border-radius:50%;display:inline-block}
  .sev.critical{background:var(--critical-soft);color:var(--critical)} .sev.critical i{background:var(--critical)}
  .sev.warning{background:var(--warning-soft);color:var(--warning)} .sev.warning i{background:var(--warning)}
  .sev.info{background:var(--info-soft);color:var(--info)} .sev.info i{background:var(--info)}
  .sev.neutral{background:#eef1f4;color:var(--muted)} .sev.neutral i{background:var(--faint)}
  .card .e{margin-top:12px;font-size:12px;color:#4a5568;border-top:1px solid var(--line2);padding-top:12px;display:flex;gap:10px;align-items:baseline}
  .card .e b{font-family:'JetBrains Mono',monospace;color:var(--ink);font-size:12px}
  .card .e::before{content:"";width:8px;height:8px;border-radius:50%;background:var(--amber);flex-shrink:0;align-self:center}
  .card .meta{margin-top:8px;font-size:11.5px;color:var(--faint);display:flex;gap:16px}
  .card .meta b{color:var(--muted);font-weight:600}
  .card .actions{margin-top:14px;padding-top:14px;border-top:1px solid var(--line2);display:flex;gap:8px;flex-wrap:wrap}
  .card .actions form{margin:0}
  .card button{border:1px solid var(--line);background:var(--surface);border-radius:8px;padding:8px 15px;font-size:12px;cursor:pointer;color:#3d4550;font-weight:500;font-family:inherit;letter-spacing:.01em}
  .card button:hover{border-color:#b9c2cc;background:#f8f9fa}
  .card button.yes{background:var(--ink);color:#fff;border-color:var(--ink)}
  .card button.yes:hover{background:#2b3138}
  .card.done{opacity:.55}
  .chip{font-family:'JetBrains Mono',monospace;font-size:11px;background:#f1f3f5;border:1px solid var(--line2);border-radius:6px;padding:2px 8px;color:#4a5568}
  .empty{background:var(--surface);border:1.5px dashed #d5dae1;border-radius:12px;padding:44px 24px;text-align:center;color:var(--muted);font-size:13px}
  .empty .mark{display:inline-grid;place-items:center;width:36px;height:36px;border-radius:50%;background:var(--ok-soft);color:var(--ok);font-weight:700;font-size:16px;margin-bottom:12px}

  @media (max-width:880px){
    .shell{grid-template-columns:1fr}
    .side{position:static;height:auto;flex-direction:row;flex-wrap:wrap;align-items:center}
    .side .brand{border:none;padding-bottom:4px}
    .nav{flex-direction:row;flex-wrap:wrap;margin-top:0}
    .nav .sect{display:none}
    .nav a .n{display:none}
    .side .foot{border-top:none;margin-left:auto;display:flex;gap:14px;align-items:center;padding-top:0}
    .side .who{padding:0}
    .main{padding:20px 16px}
    .statgrid{grid-template-columns:1fr 1fr}
    .tworow{grid-template-columns:1fr}
  }
</style>
</head>
<body>
@php $page = trim($__env->yieldContent('page')); @endphp
<div class="shell">
  <aside class="side">
    <a class="brand" href="{{ route('app') }}"><span class="dot"></span>ALLOCORE <span class="tag">{{ __('ui.Manager') }}</span></a>
    <nav class="nav">
      <div class="sect">{{ __('ui.decision intelligence') }}</div>
      <a href="{{ route('app') }}" class="{{ $page === 'dashboard' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        {{ __('ui.Overview') }}</a>
      <a href="{{ route('recommendations') }}" class="{{ $page === 'recommendations' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        {{ __('ui.Recommendations') }}@if(!empty($navOpen))<span class="n">{{ $navOpen }}</span>@endif</a>
      <a href="{{ route('signals') }}" class="{{ $page === 'signals' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="15 7 21 7 21 13"/></svg>
        {{ __('ui.Signals') }}</a>
      <a href="{{ route('companies') }}" class="{{ $page === 'companies' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
        {{ __('ui.Companies') }}</a>
      <a href="{{ route('settings') }}" class="{{ $page === 'settings' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.01a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.01a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.01a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        {{ __('ui.Settings') }}</a>
      @if($user->role === 'allocore')
      <div class="sect">{{ __('ui.Administration') }}</div>
      <a href="{{ route('sources') }}" class="{{ $page === 'sources' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
        {{ __('ui.Sources') }}</a>
      <a href="{{ route('users') }}" class="{{ $page === 'users' ? 'on' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        {{ __('ui.Users') }}</a>
      @endif
    </nav>
    <div class="foot">
      <div class="who">
        <div class="uname">{{ $user->name }}</div>
        <span class="role">{{ __('ui.'.$user->role) }}</span>
      </div>
      <div class="footrow">
        <a class="lang" href="{{ route('lang', app()->getLocale() === 'de' ? 'en' : 'de') }}">{{ app()->getLocale() === 'de' ? 'EN' : 'DE' }}</a>
        <form method="post" action="/logout">@csrf<button type="submit">{{ __('ui.Logout') }}</button></form>
      </div>
    </div>
  </aside>
  <div class="content">
    <div class="topbar">
      <div class="crumb">ALLOCORE MANAGER <b>/</b> <b>@yield('title')</b></div>
      <div class="crumb">{{ __('ui.decision intelligence') }}</div>
    </div>
    <div class="main">
      @if(session('status'))<div class="note">{{ __(session('status')) }}</div>@endif
      @yield('content')
    </div>
  </div>
</div>
</body>
</html>
