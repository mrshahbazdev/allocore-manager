@extends('layout')
@section('page', 'signals')
@section('title', __('ui.Signals'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Signals') }}</h1>
    <p>{{ __('ui.signals_sub') }}</p>
  </div>
</div>

@if($signals->count())
  <table>
    <tr><th>{{ __('ui.Signal type') }}</th><th>{{ __('ui.Company') }}</th><th>{{ __('ui.Source') }}</th><th>{{ __('ui.Occurred') }}</th></tr>
    @foreach($signals as $s)
      <tr>
        <td><span class="chip">{{ $s->type }}</span></td>
        <td>{{ $s->company_key ?? '—' }}</td>
        <td>{{ $s->source->name ?? '—' }}</td>
        <td class="num">{{ $s->occurred_at?->format('d.m.Y H:i') }}</td>
      </tr>
    @endforeach
  </table>
  {{ $signals->links() }}
@else
  <div class="empty"><span class="mark">✓</span><div>{{ __('ui.No signals yet.') }}</div></div>
@endif
@endsection
