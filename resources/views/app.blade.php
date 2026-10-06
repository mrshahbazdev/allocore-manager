<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#14181d">
<title>Allocore Manager — {{ __('ui.Your next actions') }}</title>
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
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:'Sora',ui-sans-serif,system-ui,sans-serif;background:var(--canvas);color:var(--ink);font-size:14px;-webkit-font-smoothing:antialiased}

  header{background:#14181d;color:#fff;height:56px;display:flex;justify-content:space-between;align-items:center;padding:0 24px;position:sticky;top:0;z-index:20}
  header .brand{display:flex;align-items:center;gap:11px;font-size:14px;font-weight:700;letter-spacing:.06em}
  header .brand .dot{width:8px;height:8px;border-radius:50%;background:var(--amber)}
  header .brand .tag{font-weight:500;color:#8b95a3;font-size:11px;letter-spacing:.05em;text-transform:uppercase}
  header .who{display:flex;align-items:center;gap:14px;font-size:12.5px;color:#aab4c0}
  header .who .uname{color:#e3e8ee;font-weight:500}
  header .role{border:1px solid #2c343e;border-radius:6px;padding:3px 9px;font-size:10.5px;letter-spacing:.06em;text-transform:uppercase;color:#c9d2db}
  header .lang{color:var(--amber);font-weight:700;text-decoration:none;font-size:11.5px;letter-spacing:.08em}
  header form{display:inline}
  header button{background:transparent;border:1px solid #2c343e;color:#c9d2db;border-radius:6px;padding:5px 12px;cursor:pointer;font-size:11.5px;font-family:inherit;letter-spacing:.03em}
  header button:hover{background:#1e242b}

  .page{max-width:1080px;margin:0 auto;padding:32px 24px 72px;display:grid;grid-template-columns:280px 1fr;gap:28px;align-items:start}

  .rail{position:sticky;top:76px}
  .eyebrow{font-size:10.5px;text-transform:uppercase;letter-spacing:.12em;color:var(--faint);font-weight:600;margin-bottom:8px}
  .hero h1{font-size:26px;margin:0 0 6px;letter-spacing:-.02em;font-weight:700}
  .hero p{margin:0 0 24px;color:var(--muted);font-size:12.5px;line-height:1.6;max-width:220px}
  .note{background:var(--warning-soft);border:1px solid #f0dcae;border-radius:10px;padding:11px 14px;font-size:12px;color:var(--warning);margin-bottom:20px}

  .stats{display:flex;flex-direction:column;gap:0;background:var(--surface);border:1px solid var(--line);border-radius:12px;overflow:hidden}
  .stat{display:flex;justify-content:space-between;align-items:baseline;padding:14px 16px;border-bottom:1px solid var(--line2)}
  .stat:last-child{border-bottom:none}
  .stat b{font-family:'JetBrains Mono',monospace;font-size:20px;font-weight:700;letter-spacing:-.02em}
  .stat span{color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.07em}

  .rail h2, .feed h2{font-size:10.5px;text-transform:uppercase;letter-spacing:.12em;color:var(--faint);margin:26px 0 10px;font-weight:700}
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

  .card{background:var(--surface);border:1px solid var(--line);border-radius:12px;padding:18px 20px 16px;margin-bottom:12px;transition:box-shadow .15s,border-color .15s}
  .card:hover{border-color:#d5dae0;box-shadow:0 2px 8px rgba(20,24,29,.05)}
  .card .top{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:6px}
  .card .t{font-weight:700;font-size:15px;line-height:1.35;letter-spacing:-.01em}
  .card .d{font-size:13px;color:#3d4550;line-height:1.6;max-width:58ch}
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
  .empty{background:var(--surface);border:1.5px dashed #d5dae1;border-radius:12px;padding:44px 24px;text-align:center;color:var(--muted);font-size:13px}
  .empty .mark{display:inline-grid;place-items:center;width:36px;height:36px;border-radius:50%;background:var(--ok-soft);color:var(--ok);font-weight:700;font-size:16px;margin-bottom:12px}

  footer{max-width:1080px;margin:0 auto;padding:0 24px 40px;color:var(--faint);font-size:11px;letter-spacing:.03em}
  footer .rule{border-top:1px solid var(--line);padding-top:20px}

  @media (max-width:880px){
    .page{grid-template-columns:1fr;padding:24px 16px 56px}
    .rail{position:static}
    .hero p{max-width:none}
    .stats{flex-direction:row;flex-wrap:wrap}
    .stat{flex:1 1 45%;border-bottom:none;border-right:1px solid var(--line2);flex-direction:column;align-items:flex-start;gap:2px}
  }
</style>
</head>
<body>
<header>
  <div class="brand"><span class="dot"></span>ALLOCORE <span class="tag">{{ __('ui.Manager') }} · {{ __('ui.decision intelligence') }}</span></div>
  <div class="who">
    <a class="lang" href="{{ route('lang', app()->getLocale() === 'de' ? 'en' : 'de') }}">{{ app()->getLocale() === 'de' ? 'EN' : 'DE' }}</a>
    <span class="uname">{{ $user->name }}</span> <span class="role">{{ __('ui.'.$user->role) }}</span>
    <form method="post" action="/logout">@csrf<button type="submit">{{ __('ui.Logout') }}</button></form>
  </div>
</header>
<div class="page">
<aside class="rail">
  @if(session('status'))<div class="note">{{ __(session('status')) }}</div>@endif
  <div class="hero">
    <div class="eyebrow">{{ __('ui.decision intelligence') }}</div>
    <h1>{{ __('ui.Your next actions') }}</h1>
    <p>{{ __('ui.hero_sub') }}</p>
  </div>
  <div class="stats">
    <div class="stat"><b>{{ $stats['open'] }}</b><span>{{ __('ui.open') }}</span></div>
    <div class="stat"><b>{{ $stats['done'] }}</b><span>{{ __('ui.decided') }}</span></div>
    <div class="stat"><b>{{ $stats['signals'] }}</b><span>{{ __('ui.signals') }}</span></div>
    <div class="stat"><b>{{ $stats['success_rate'] !== null ? $stats['success_rate'].'%' : '—' }}</b><span>{{ __('ui.success rate') }}</span></div>
  </div>

  @if($disavoIntel)
    <h2>{{ __('ui.Portfolio overview') }}</h2>
    <div class="panel">
      <div class="row"><span class="k">{{ __('ui.companies reporting') }}</span><b>{{ $disavoIntel['companies'] }}</b></div>
      <div class="row"><span class="k">{{ __('ui.recommendations issued') }}</span><b>{{ $disavoIntel['recommendations_total'] }}</b></div>
      <div class="row"><span class="k">{{ __('ui.outcomes measured') }}</span><b>{{ $disavoIntel['resolved'] }}</b></div>
      <div class="row"><span class="k">{{ __('ui.critical open') }}</span><b>{{ $disavoIntel['critical_open'] }}</b></div>
    </div>
  @endif
</aside>
<main class="feed">
  @if($allocoreIntel)
    <h2>{{ __('ui.Learning performance') }}</h2>
    <table>
      <tr><th>{{ __('ui.Pattern') }}</th><th>{{ __('ui.Recommendations') }}</th><th>{{ __('ui.Companies tried') }}</th><th>{{ __('ui.Success rate') }}</th></tr>
      @foreach($allocoreIntel['effectiveness'] as $e)
        <tr><td>{{ $e['code'] }}</td><td class="num">{{ $e['recommendations'] }}</td><td class="num">{{ $e['tried'] }}</td><td class="num">{{ $e['success_rate'] }}%</td></tr>
      @endforeach
    </table>
    <h2>{{ __('ui.Company') }}</h2>
    <table>
      <tr><th>{{ __('ui.Company') }}</th><th>{{ __('ui.Signals') }}</th></tr>
      @foreach($allocoreIntel['clusters'] as $c)
        <tr><td>{{ $c->company_key }}</td><td class="num">{{ $c->n }}</td></tr>
      @endforeach
    </table>
  @endif

  @if($platformIntel)
    <h2>{{ __('ui.Platform intelligence') }}</h2>
    <table>
      <tr><th>{{ __('ui.Signal type') }}</th><th>{{ __('ui.Count') }}</th></tr>
      @foreach($platformIntel['by_type'] as $t)
        <tr><td>{{ $t->type }}</td><td class="num">{{ $t->n }}</td></tr>
      @endforeach
    </table>
    <table>
      <tr><th>{{ __('ui.Challenge') }}</th><th>{{ __('ui.Companies affected') }}</th></tr>
      @foreach($platformIntel['top_challenges'] as $p)
        <tr><td>{{ $p->challenge }}</td><td class="num">{{ $p->companies_count }}</td></tr>
      @endforeach
    </table>
  @endif

  <h2>{{ __('ui.Recommendations for you') }}</h2>
  @forelse($open as $rec)
    <div class="card">
      <div class="top">
        <div class="t">{{ $rec->localizedTitle() }}</div>
        <span class="sev {{ $rec->severity }}"><i></i>{{ __('ui.'.$rec->severity) }}</span>
      </div>
      <div class="d">{{ $rec->localizedDescription() }}</div>
      @php $tried=(int)($rec->evidence['companies_tried']??0); @endphp
      @if($tried>0)<div class="e"><b>{{ __('ui.evidence', ['tried'=>$tried,'rate'=>$rec->evidence['success_rate']??0]) }}</b></div>@endif
      @if(!empty($rec->evidence['effort']) || !empty($rec->evidence['responsible']))
        <div class="meta">
          @if(!empty($rec->evidence['effort']))<span><b>{{ __('ui.Effort') }}</b> · {{ __('ui.'.ucfirst($rec->evidence['effort'])) }}</span>@endif
          @if(!empty($rec->evidence['responsible']))<span><b>{{ __('ui.Responsible') }}</b> · {{ $rec->evidence['responsible'] }}</span>@endif
        </div>
      @endif
      <div class="actions">
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="success"><button class="yes" type="submit">{{ __('ui.Done — it worked') }}</button></form>
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="failed"><button type="submit">{{ __("ui.Didn't work") }}</button></form>
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="dismissed"><button type="submit">{{ __('ui.Not relevant') }}</button></form>
      </div>
    </div>
  @empty
    <div class="empty"><span class="mark">✓</span><div>{{ __('ui.All clear — no open recommendations right now.') }}</div></div>
  @endforelse

  @if($done->count())
    <h2>{{ __('ui.Decided') }}</h2>
    @foreach($done as $rec)
      <div class="card done">
        <div class="top">
          <div class="t">{{ $rec->localizedTitle() }}</div>
          <span class="sev neutral"><i></i>{{ __('ui.'.($rec->latestOutcome?->result ?? $rec->status)) }}</span>
        </div>
        <div class="d">{{ $rec->localizedDescription() }}</div>
      </div>
    @endforeach
  @endif
</main>
</div>
<footer><div class="rule">Allocore Manager · DISAVO ecosystem</div></footer>
</body>
</html>
