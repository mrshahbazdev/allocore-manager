@extends('layout')
@section('page','patterns')
@section('title', __('ui.Patterns'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Patterns') }}</h1>
    <p>{{ __('ui.patterns_sub') }}</p>
  </div>
</div>

<table>
  <thead><tr>
    <th>{{ __('ui.Rule') }}</th>
    <th>{{ __('ui.Challenge') }}</th>
    <th class="num">{{ __('ui.Companies affected') }}</th>
    <th class="num">{{ __('ui.Signals (30d)') }}</th>
    <th class="num">{{ __('ui.Open') }}</th>
    <th class="num">{{ __('ui.Recommendations') }}</th>
  </tr></thead>
  <tbody>
  @forelse($patterns as $p)
    <tr>
      <td><a href="{{ route('recommendations', ['code' => $p->code]) }}" class="chip" style="text-decoration:none;color:var(--accent)">{{ $p->code }}</a></td>
      <td><b>{{ $p->challenge }}</b></td>
      <td class="num">{{ $p->companies_count }}</td>
      <td class="num">{{ $p->evidence['signals_30d'] ?? $p->evidence['suggestions'] ?? 0 }}</td>
      <td class="num">{{ $p->open_recs }}</td>
      <td class="num">{{ $p->total_recs }}</td>
    </tr>
  @empty
    <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:28px">{{ __('ui.No patterns yet.') }}</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
