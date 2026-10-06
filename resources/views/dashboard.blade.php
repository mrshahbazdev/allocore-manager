@extends('layout')
@section('page', 'dashboard')
@section('title', __('ui.Overview'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Your next actions') }}</h1>
    <p>{{ __('ui.hero_sub') }}</p>
  </div>
</div>

<div class="card" style="margin-bottom:16px">
  <div class="k" style="font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.AI Coach') }}</div>
  <div style="font-size:14.5px;line-height:1.5">{{ $coach }}</div>
</div>

<div class="statgrid">
  <div class="statcard"><div class="k">{{ __('ui.open') }}</div><div class="v {{ $stats['open'] ? 'amber' : '' }}">{{ $stats['open'] }}</div></div>
  <div class="statcard"><div class="k">{{ __('ui.decided') }}</div><div class="v">{{ $stats['done'] }}</div></div>
  <div class="statcard"><div class="k">{{ __('ui.signals') }}</div><div class="v">{{ $stats['signals'] }}</div></div>
  <div class="statcard"><div class="k">{{ __('ui.success rate') }}</div><div class="v">{{ $stats['success_rate'] !== null ? $stats['success_rate'].'%' : '—' }}</div></div>
</div>

@if($disavoIntel)
  <h2 class="sect">{{ __('ui.Portfolio overview') }}</h2>
  <div class="panel">
    <div class="row"><span class="k">{{ __('ui.companies reporting') }}</span><b>{{ $disavoIntel['companies'] }}</b></div>
    <div class="row"><span class="k">{{ __('ui.recommendations issued') }}</span><b>{{ $disavoIntel['recommendations_total'] }}</b></div>
    <div class="row"><span class="k">{{ __('ui.outcomes measured') }}</span><b>{{ $disavoIntel['resolved'] }}</b></div>
    <div class="row"><span class="k">{{ __('ui.critical open') }}</span><b>{{ $disavoIntel['critical_open'] }}</b></div>
  </div>
@endif

@if($allocoreIntel)
  <h2 class="sect">{{ __('ui.Learning performance') }}</h2>
  <table>
    <tr><th>{{ __('ui.Pattern') }}</th><th>{{ __('ui.Recommendations') }}</th><th>{{ __('ui.Companies tried') }}</th><th>{{ __('ui.Success rate') }}</th></tr>
    @foreach($allocoreIntel['effectiveness'] as $e)
      <tr><td>{{ $e['code'] }}</td><td class="num">{{ $e['recommendations'] }}</td><td class="num">{{ $e['tried'] }}</td><td class="num">{{ $e['success_rate'] }}%</td></tr>
    @endforeach
  </table>
@endif

@if($platformIntel)
  <h2 class="sect">{{ __('ui.Platform intelligence') }}</h2>
  <div class="tworow">
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
  </div>
@endif

<h2 class="sect">{{ __('ui.Recommendations for you') }} <a href="{{ route('recommendations') }}">{{ __('ui.View all') }} →</a></h2>
@forelse($open->take(4) as $rec)
  @include('recommendation-card')
@empty
  <div class="empty"><span class="mark">✓</span><div>{{ __('ui.All clear — no open recommendations right now.') }}</div></div>
@endforelse

@if($recentSignals->count())
  <h2 class="sect">{{ __('ui.Latest signals') }} <a href="{{ route('signals') }}">{{ __('ui.View all') }} →</a></h2>
  <table>
    <tr><th>{{ __('ui.Signal type') }}</th><th>{{ __('ui.Company') }}</th><th>{{ __('ui.Source') }}</th><th>{{ __('ui.Occurred') }}</th></tr>
    @foreach($recentSignals as $s)
      <tr>
        <td><span class="chip">{{ $s->type }}</span></td>
        <td>{{ $s->company_key ?? '—' }}</td>
        <td>{{ $s->source->name ?? '—' }}</td>
        <td class="num">{{ $s->occurred_at?->format('d.m.Y H:i') }}</td>
      </tr>
    @endforeach
  </table>
@endif
@endsection
