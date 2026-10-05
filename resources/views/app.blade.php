<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Allocore Manager — {{ __('ui.Your next actions') }}</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;background:#f4f6f9;color:#16202e}
  header{background:linear-gradient(90deg,#101b2b,#1a2b44);color:#fff;padding:0 24px;height:60px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10;box-shadow:0 2px 12px rgba(10,20,35,.25)}
  header .brand{display:flex;align-items:center;gap:10px;font-size:15px;font-weight:700;letter-spacing:.02em}
  header .brand .dot{width:9px;height:9px;border-radius:50%;background:#ff9200;box-shadow:0 0 10px #ff920080}
  header .brand small{font-weight:400;color:#93a4bb;font-size:12.5px}
  header .who{display:flex;align-items:center;gap:12px;font-size:13px;color:#cbd5e1}
  header .role{background:#ffffff14;border-radius:99px;padding:3px 11px;font-size:11.5px;letter-spacing:.03em}
  header form{display:inline}
  header button{background:#ffffff0d;border:1px solid #3a4a5e;color:#cbd5e1;border-radius:8px;padding:6px 13px;cursor:pointer;font-size:12.5px}
  header button:hover{background:#ffffff1c}
  main{max-width:760px;margin:0 auto;padding:28px 18px 64px}
  .hero{margin-bottom:24px}
  .hero h1{font-size:24px;margin:0 0 5px;letter-spacing:-.01em}
  .hero p{margin:0;color:#64748b;font-size:14px;max-width:520px}
  .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:8px}
  .stat{background:#fff;border:1px solid #e6eaf0;border-radius:12px;padding:14px 16px}
  .stat b{font-size:22px;display:block;line-height:1.15}
  .stat span{color:#8a97a8;font-size:12px}
  .note{background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:11px 15px;font-size:13px;margin-bottom:18px}
  h2{font-size:11.5px;text-transform:uppercase;letter-spacing:.1em;color:#8a97a8;margin:32px 0 12px;font-weight:700}
  .card{background:#fff;border:1px solid #e6eaf0;border-radius:16px;padding:20px 22px;margin-bottom:14px;box-shadow:0 1px 3px rgba(15,23,42,.05);transition:box-shadow .15s}
  .card:hover{box-shadow:0 4px 14px rgba(15,23,42,.08)}
  .card.sev-critical{border-left:4px solid #dc2626}
  .card.sev-warning{border-left:4px solid #f59e0b}
  .card.sev-info{border-left:4px solid #ff9200}
  .card .top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
  .card .t{font-weight:700;font-size:15.5px;line-height:1.35}
  .card .d{margin-top:7px;font-size:14px;color:#33404f;line-height:1.55}
  .pill{flex-shrink:0;font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:4px 10px;border-radius:99px}
  .pill.critical{background:#fee2e2;color:#b91c1c}
  .pill.warning{background:#fef3c7;color:#a16207}
  .pill.info{background:#fff1e0;color:#c2630a}
  .card .e{margin-top:12px;font-size:12.5px;color:#4a5a6d;background:#f4f6f9;border-radius:9px;padding:10px 13px;display:flex;gap:9px;align-items:center}
  .card .e::before{content:"◉";color:#ff9200;font-size:11px}
  .card .meta{margin-top:9px;font-size:12px;color:#8a97a8}
  .card .actions{margin-top:15px;display:flex;gap:8px;flex-wrap:wrap}
  .card button{border:1px solid #dbe2ea;background:#fff;border-radius:9px;padding:8px 15px;font-size:13px;cursor:pointer;color:#33404f;font-weight:500}
  .card button:hover{border-color:#aab8c8;background:#f8fafc}
  .card button.yes{background:#16202e;color:#fff;border-color:#16202e}
  .card button.yes:hover{background:#243349}
  .card.done{opacity:.55}
  .panel{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px}
  .panel .p{background:#fff;border:1px solid #e6eaf0;border-radius:14px;padding:18px}
  .panel .p b{font-size:26px;display:block;line-height:1.1}
  .panel .p span{color:#8a97a8;font-size:12px;display:block;margin-top:3px}
  table{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border:1px solid #e6eaf0;border-radius:14px;overflow:hidden;font-size:13.5px;margin-bottom:14px}
  th,td{padding:11px 16px;border-bottom:1px solid #eef1f5;text-align:left}
  th{background:#f8fafc;color:#8a97a8;font-size:10.5px;text-transform:uppercase;letter-spacing:.07em}
  tr:last-child td{border-bottom:none}
  tr:hover td{background:#fafbfc}
  .empty{background:#fff;border:1.5px dashed #d3dae3;border-radius:16px;padding:40px;text-align:center;color:#8a97a8;font-size:14px}
  .empty .big{font-size:28px;margin-bottom:8px}
  footer{max-width:760px;margin:0 auto;padding:0 18px 40px;color:#aab6c5;font-size:11.5px;text-align:center}
  @media (max-width:560px){main{padding:20px 12px}.card{padding:16px}.hero h1{font-size:20px}}
</style>
</head>
<body>
<header>
  <div class="brand"><span class="dot"></span>ALLOCORE <small>{{ __('ui.Manager') }} · {{ __('ui.decision intelligence') }}</small></div>
  <div class="who">
    <a href="{{ route('lang', app()->getLocale() === 'de' ? 'en' : 'de') }}" style="color:#ff9200;font-weight:700;text-decoration:none">{{ app()->getLocale() === 'de' ? 'EN' : 'DE' }}</a>
    {{ $user->name }} <span class="role">{{ __('ui.'.$user->role) }}</span>
    <form method="post" action="/logout">@csrf<button type="submit">{{ __('ui.Logout') }}</button></form>
  </div>
</header>
<main>
  @if(session('status'))<div class="note">{{ __(session('status')) }}</div>@endif

  <div class="hero">
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
        <span class="pill {{ $rec->severity }}">{{ __('ui.'.$rec->severity) }}</span>
      </div>
      <div class="d">{{ $rec->description }}</div>
      @php $tried=(int)($rec->evidence['companies_tried']??0); @endphp
      @if($tried>0)<div class="e">{{ __('ui.evidence', ['tried'=>$tried,'rate'=>$rec->evidence['success_rate']??0]) }}</div>@endif
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
          <span class="pill info">{{ __('ui.'.($rec->latestOutcome?->result ?? $rec->status)) }}</span>
        </div>
        <div class="d">{{ $rec->description }}</div>
      </div>
    @endforeach
  @endif
</main>
<footer>Allocore Manager · DISAVO ecosystem</footer>
</body>
</html>
