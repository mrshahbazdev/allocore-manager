<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Allocore Manager — {{ __('ui.Your next actions') }}</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:#f6f7f9;color:#16202e}
  header{background:#16202e;color:#fff;padding:0 24px;height:58px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10}
  header .brand{display:flex;align-items:center;gap:10px;font-size:15px;font-weight:700;letter-spacing:.02em}
  header .brand .dot{width:9px;height:9px;border-radius:50%;background:#ff9200}
  header .brand small{font-weight:400;color:#94a3b8;font-size:12.5px}
  header .who{display:flex;align-items:center;gap:10px;font-size:13px;color:#cbd5e1}
  header form{display:inline}
  header button{background:transparent;border:1px solid #3a4a5e;color:#cbd5e1;border-radius:7px;padding:6px 13px;cursor:pointer;font-size:12.5px}
  header button:hover{background:#233040}
  main{max-width:780px;margin:0 auto;padding:30px 18px 60px}
  .hello{margin-bottom:22px}
  .hello h1{font-size:22px;margin:0 0 4px}
  .hello p{margin:0;color:#64748b;font-size:14px}
  .chips{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px}
  .chip{background:#fff;border:1px solid #e6eaf0;border-radius:99px;padding:7px 14px;font-size:12.5px;color:#475569}
  .chip b{color:#16202e;font-weight:700;margin-right:4px}
  .note{background:#fffbeb;border:1px solid #fde68a;border-radius:9px;padding:10px 14px;font-size:13px;margin-bottom:18px}
  h2{font-size:12px;text-transform:uppercase;letter-spacing:.09em;color:#8a97a8;margin:30px 0 12px;font-weight:700}
  .card{background:#fff;border:1px solid #e6eaf0;border-radius:14px;padding:18px 20px;margin-bottom:14px;box-shadow:0 1px 2px rgba(15,23,42,.04)}
  .card.sev-critical{border-left:4px solid #dc2626}
  .card.sev-warning{border-left:4px solid #f59e0b}
  .card.sev-info{border-left:4px solid #ff9200}
  .card .top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px}
  .card .t{font-weight:700;font-size:15px;line-height:1.35}
  .card .d{margin-top:6px;font-size:14px;color:#33404f;line-height:1.5}
  .pill{flex-shrink:0;font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:3px 9px;border-radius:99px}
  .pill.critical{background:#fee2e2;color:#b91c1c}
  .pill.warning{background:#fef3c7;color:#a16207}
  .pill.info{background:#fff1e0;color:#c2630a}
  .card .e{margin-top:10px;font-size:12.5px;color:#5b6a7d;background:#f4f6f9;border-radius:8px;padding:9px 12px;display:flex;gap:8px;align-items:center}
  .card .e::before{content:"◉";color:#ff9200;font-size:10px}
  .card .meta{margin-top:8px;font-size:12px;color:#8a97a8}
  .card .actions{margin-top:14px;display:flex;gap:8px;flex-wrap:wrap}
  .card button{border:1px solid #dbe2ea;background:#fff;border-radius:8px;padding:8px 14px;font-size:13px;cursor:pointer;color:#33404f}
  .card button:hover{border-color:#b9c4d0}
  .card button.yes{background:#16202e;color:#fff;border-color:#16202e}
  .card.done{opacity:.6}
  .panel{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
  .panel .p{background:#fff;border:1px solid #e6eaf0;border-radius:12px;padding:16px}
  .panel .p b{font-size:24px;display:block}
  .panel .p span{color:#8a97a8;font-size:12px}
  table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e6eaf0;border-radius:12px;overflow:hidden;font-size:13.5px;margin-bottom:14px}
  th,td{padding:10px 14px;border-bottom:1px solid #eef1f5;text-align:left}
  th{background:#f8fafc;color:#8a97a8;font-size:11px;text-transform:uppercase;letter-spacing:.06em}
  tr:last-child td{border-bottom:none}
  .empty{background:#fff;border:1.5px dashed #d3dae3;border-radius:14px;padding:34px;text-align:center;color:#8a97a8;font-size:14px}
  .empty .big{font-size:26px;margin-bottom:8px}
  @media (max-width:560px){main{padding:20px 12px}.card{padding:15px}}
</style>
</head>
<body>
<header>
  <div class="brand"><span class="dot"></span>ALLOCORE <small>{{ __('ui.Manager') }} · {{ __('ui.decision intelligence') }}</small></div>
  <div class="who">
    <a href="{{ route('lang', app()->getLocale() === 'de' ? 'en' : 'de') }}" style="color:#ff9200;font-weight:700;text-decoration:none">{{ app()->getLocale() === 'de' ? 'EN' : 'DE' }}</a>
    {{ $user->name }} · {{ $user->role }}
    <form method="post" action="/logout">@csrf<button type="submit">{{ __('ui.Logout') }}</button></form>
  </div>
</header>
<main>
  @if(session('status'))<div class="note">{{ session('status') }}</div>@endif

  <div class="hello">
    <h1>{{ __('ui.Your next actions') }}</h1>
    <p>{{ __('ui.hero_sub') }}</p>
  </div>

  <div class="chips">
    <div class="chip"><b>{{ $stats['open'] }}</b> {{ __('ui.open') }}</div>
    <div class="chip"><b>{{ $stats['done'] }}</b> {{ __('ui.decided') }}</div>
    <div class="chip"><b>{{ $stats['signals'] }}</b> {{ __('ui.signals') }}</div>
    <div class="chip"><b>{{ $stats['success_rate'] !== null ? $stats['success_rate'].'%' : '—' }}</b> {{ __('ui.success rate') }}</div>
  </div>

  @if($disavoIntel)
    <h2>{{ __('ui.Portfolio overview') }}</h2>
    <div class="panel">
      <div class="p"><b>{{ $disavoIntel['companies'] }}</b><span>{{ __('ui.companies reporting') }}</span></div>
      <div class="p"><b>{{ $disavoIntel['recommendations_total'] }}</b><span>{{ __('ui.recommendations issued') }}</span></div>
      <div class="p"><b>{{ $disavoIntel['resolved'] }}</b><span>{{ __('ui.outcomes measured') }}</span></div>
      <div class="p"><b>{{ $disavoIntel['critical_open'] }}</b><span>{{ __('ui.critical open') }}</span></div>
    </div>
  @endif

  @if($allocoreIntel)
    <h2>{{ __('ui.Learning performance') }}</h2>
    <table>
      <tr><th>{{ __('ui.Pattern') }}</th><th>{{ __('ui.Recommendations') }}</th><th>{{ __('ui.Companies tried') }}</th><th>{{ __('ui.Success rate') }}</th></tr>
      @foreach($allocoreIntel['effectiveness'] as $e)
        <tr><td>{{ $e['code'] }}</td><td>{{ $e['recommendations'] }}</td><td>{{ $e['tried'] }}</td><td>{{ $e['success_rate'] }}%</td></tr>
      @endforeach
    </table>
    <table>
      <tr><th>{{ __('ui.Company') }}</th><th>{{ __('ui.Signals') }}</th></tr>
      @foreach($allocoreIntel['clusters'] as $c)
        <tr><td>{{ $c->company_key }}</td><td>{{ $c->n }}</td></tr>
      @endforeach
    </table>
  @endif

  @if($platformIntel)
    <h2>{{ __('ui.Platform intelligence') }}</h2>
    <table>
      <tr><th>{{ __('ui.Signal type') }}</th><th>{{ __('ui.Count') }}</th></tr>
      @foreach($platformIntel['by_type'] as $t)
        <tr><td>{{ $t->type }}</td><td>{{ $t->n }}</td></tr>
      @endforeach
    </table>
    <table>
      <tr><th>{{ __('ui.Challenge') }}</th><th>{{ __('ui.Companies affected') }}</th></tr>
      @foreach($platformIntel['top_challenges'] as $p)
        <tr><td>{{ $p->challenge }}</td><td>{{ $p->companies_count }}</td></tr>
      @endforeach
    </table>
  @endif

  <h2>{{ __('ui.Recommendations for you') }}</h2>
  @forelse($open as $rec)
    <div class="card sev-{{ $rec->severity }}">
      <div class="top">
        <div class="t">{{ $rec->title }}</div>
        <span class="pill {{ $rec->severity }}">{{ $rec->severity }}</span>
      </div>
      <div class="d">{{ $rec->description }}</div>
      @if(!empty($rec->evidence['text']))<div class="e">{{ $rec->evidence['text'] }}</div>@endif
      @if(!empty($rec->evidence['effort']) || !empty($rec->evidence['responsible']))
        <div class="meta">
          @if(!empty($rec->evidence['effort'])){{ __('ui.Effort') }}: {{ __('ui.'.ucfirst($rec->evidence['effort'])) }}@endif
          @if(!empty($rec->evidence['responsible'])) · {{ __('ui.Responsible') }}: {{ $rec->evidence['responsible'] }}@endif
        </div>
      @endif
      <div class="actions">
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="success"><button class="yes" type="submit">{{ __('ui.Done — it worked') }}</button></form>
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="failed"><button type="submit">{{ __("ui.Didn't work") }}</button></form>
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="dismissed"><button type="submit">{{ __('ui.Not relevant') }}</button></form>
      </div>
    </div>
  @empty
    <div class="empty"><div class="big">✓</div>{{ __('ui.All clear — no open recommendations right now.') }}</div>
  @endforelse

  @if($done->count())
    <h2>{{ __('ui.Decided') }}</h2>
    @foreach($done as $rec)
      <div class="card done">
        <div class="top">
          <div class="t">{{ $rec->title }}</div>
          <span class="pill info">{{ $rec->latestOutcome?->result ?? $rec->status }}</span>
        </div>
        <div class="d">{{ $rec->description }}</div>
      </div>
    @endforeach
  @endif
</main>
</body>
</html>
