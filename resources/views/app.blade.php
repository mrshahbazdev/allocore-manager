<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Allocore Manager</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:ui-sans-serif,system-ui;background:#f1f5f9;color:#0f172a}
  header{background:#0f172a;color:#fff;padding:14px 24px;display:flex;justify-content:space-between;align-items:center}
  header h1{font-size:16px;margin:0}
  header .who{font-size:13px;color:#94a3b8}
  header form{display:inline}
  header button{background:none;border:1px solid #475569;color:#cbd5e1;border-radius:6px;padding:5px 12px;cursor:pointer;margin-left:10px}
  main{max-width:820px;margin:28px auto;padding:0 16px}
  .stats{display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap}
  .stat{background:#fff;border-radius:10px;padding:14px 18px;flex:1;min-width:140px;border:1px solid #e2e8f0}
  .stat b{font-size:22px;display:block}.stat span{color:#64748b;font-size:12px}
  h2{font-size:15px;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin:26px 0 10px}
  .rec{background:#fff;border:1px solid #e2e8f0;border-left:4px solid #94a3b8;border-radius:10px;padding:16px 18px;margin-bottom:12px}
  .rec.critical{border-left-color:#dc2626}.rec.warning{border-left-color:#ca8a04}
  .rec .t{font-weight:700}.rec .d{margin-top:4px;font-size:14px}
  .rec .e{margin-top:8px;font-size:12.5px;color:#475569;background:#f8fafc;border-radius:6px;padding:8px 10px}
  .rec .actions{margin-top:10px;display:flex;gap:8px;flex-wrap:wrap}
  .rec button{border:1px solid #d6dee9;background:#fff;border-radius:6px;padding:6px 12px;font-size:13px;cursor:pointer}
  .rec button.primary{background:#0f172a;color:#fff;border-color:#0f172a}
  .done{opacity:.65}
  .note{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:16px}
  table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;font-size:13.5px}
  th,td{padding:9px 12px;border-bottom:1px solid #e2e8f0;text-align:left}th{background:#f8fafc;color:#64748b;font-size:12px;text-transform:uppercase}
  .empty{background:#fff;border:1px dashed #cbd5e1;border-radius:10px;padding:26px;text-align:center;color:#64748b}
  .badge{font-size:11px;padding:2px 8px;border-radius:99px;background:#e2e8f0;color:#334155;vertical-align:middle;margin-left:6px}
</style>
</head>
<body>
<header>
  <h1>Allocore Manager <span style="color:#ca8a04">·</span> <span style="font-weight:400;font-size:13px;color:#94a3b8">decision intelligence</span></h1>
  <div class="who">
    {{ $user->name }} · {{ $user->role }}
    <form method="post" action="/logout">@csrf<button type="submit">Logout</button></form>
  </div>
</header>
<main>
  @if(session('status'))<div class="note">{{ session('status') }}</div>@endif

  <div class="stats">
    <div class="stat"><b>{{ $stats['signals'] }}</b><span>signals received</span></div>
    <div class="stat"><b>{{ $stats['open'] }}</b><span>open actions</span></div>
    <div class="stat"><b>{{ $stats['done'] }}</b><span>decided</span></div>
    <div class="stat"><b>{{ $stats['success_rate'] !== null ? $stats['success_rate'].'%' : '—' }}</b><span>recommendation success</span></div>
  </div>

  @if($disavoIntel)
    <h2>DISAVO — portfolio intelligence</h2>
    <div class="stats">
      <div class="stat"><b>{{ $disavoIntel['companies'] }}</b><span>companies reporting</span></div>
      <div class="stat"><b>{{ $disavoIntel['recommendations_total'] }}</b><span>recommendations issued</span></div>
      <div class="stat"><b>{{ $disavoIntel['resolved'] }}</b><span>outcomes measured</span></div>
      <div class="stat"><b>{{ $disavoIntel['critical_open'] }}</b><span>critical open</span></div>
    </div>
  @endif

  @if($allocoreIntel)
    <h2>Allocore — learning performance</h2>
    <table>
      <tr><th>Pattern</th><th>Recommendations</th><th>Companies tried</th><th>Success rate</th></tr>
      @foreach($allocoreIntel['effectiveness'] as $e)
        <tr><td>{{ $e['code'] }}</td><td>{{ $e['recommendations'] }}</td><td>{{ $e['tried'] }}</td><td>{{ $e['success_rate'] }}%</td></tr>
      @endforeach
    </table>
    <h2>Company clusters</h2>
    <table>
      <tr><th>Company</th><th>Signals</th></tr>
      @foreach($allocoreIntel['clusters'] as $c)
        <tr><td>{{ $c->company_key }}</td><td>{{ $c->n }}</td></tr>
      @endforeach
    </table>
  @endif

  @if($platformIntel)
    <h2>Platform intelligence</h2>
    <table>
      <tr><th>Signal type</th><th>Count</th></tr>
      @foreach($platformIntel['by_type'] as $t)
        <tr><td>{{ $t->type }}</td><td>{{ $t->n }}</td></tr>
      @endforeach
    </table>
    <table style="margin-top:12px">
      <tr><th>Challenge</th><th>Companies affected</th></tr>
      @foreach($platformIntel['top_challenges'] as $p)
        <tr><td>{{ $p->challenge }}</td><td>{{ $p->companies_count }}</td></tr>
      @endforeach
    </table>
  @endif

  <h2>Your next actions</h2>
  @forelse($open as $rec)
    <div class="rec {{ $rec->severity }}">
      <div class="t">{{ $rec->title }}<span class="badge">{{ $rec->severity }}</span></div>
      <div class="d">{{ $rec->description }}</div>
      @if(!empty($rec->evidence['text']))<div class="e">{{ $rec->evidence['text'] }}</div>@endif
      <div class="actions">
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="success"><button class="primary" type="submit">Done — it worked</button></form>
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="failed"><button type="submit">Done — it didn't work</button></form>
        <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="dismissed"><button type="submit">Not relevant</button></form>
      </div>
    </div>
  @empty
    <div class="empty">All clear — no open recommendations right now.</div>
  @endforelse

  @if($done->count())
    <h2>Decided</h2>
    @foreach($done as $rec)
      <div class="rec done">
        <div class="t">{{ $rec->title }}<span class="badge">{{ $rec->latestOutcome?->result ?? $rec->status }}</span></div>
        <div class="d">{{ $rec->description }}</div>
      </div>
    @endforeach
  @endif
</main>
</body>
</html>
