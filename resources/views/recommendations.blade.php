@extends('layout')
@section('page', 'recommendations')
@section('title', __('ui.Recommendations'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Recommendations') }}</h1>
    <p>{{ __('ui.hero_sub') }}</p>
  </div>
</div>

@if($codes->count())
  <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
    @foreach($codes as $c)
      <a href="{{ route('recommendations', ['code' => $c]) }}" class="chip" style="text-decoration:none;{{ $activeCode === $c ? 'outline:1px solid var(--accent)' : '' }}">{{ $c }}</a>
    @endforeach
    @if($activeCode)
      <a href="{{ route('recommendations') }}" class="chip" style="text-decoration:none">× {{ __('ui.Reset') }}</a>
    @endif
  </div>
@endif

@forelse($open as $rec)
  @include('recommendation-card')
@empty
  <div class="empty"><span class="mark">✓</span><div>{{ __('ui.All clear — no open recommendations right now.') }}</div></div>
@endforelse

@if($done->count())
  <h2 class="sect">{{ __('ui.Decided') }}</h2>
  @foreach($done as $rec)
    @include('recommendation-card')
  @endforeach
@endif
@endsection
