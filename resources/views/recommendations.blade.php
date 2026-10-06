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
