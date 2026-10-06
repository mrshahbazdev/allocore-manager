@extends('layout')
@section('page','learning')
@section('title', __('ui.Learning'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Learning') }}</h1>
    <p>{{ __('ui.learning_sub') }}</p>
  </div>
  @if($overall !== null)
    <div class="stat" style="min-width:150px">
      <span class="v">{{ $overall }}%</span>
      <span class="k">{{ __('ui.Overall success rate') }}</span>
    </div>
  @endif
</div>

<table>
  <thead><tr>
    <th>{{ __('ui.Rule') }}</th>
    <th class="num">{{ __('ui.Recommendations') }}</th>
    <th class="num">{{ __('ui.Open') }}</th>
    <th class="num">{{ __('ui.Tried') }}</th>
    <th class="num">{{ __('ui.Succeeded') }}</th>
    <th class="num">{{ __('ui.Failed') }}</th>
    <th class="num">{{ __('ui.Dismissed') }}</th>
    <th class="num">{{ __('ui.Success rate') }}</th>
  </tr></thead>
  <tbody>
  @forelse($rules as $r)
    <tr>
      <td><span class="chip">{{ $r->code }}</span></td>
      <td class="num">{{ $r->recommendations }}</td>
      <td class="num">{{ $r->open }}</td>
      <td class="num">{{ $r->tried }}</td>
      <td class="num"><b class="ok">{{ $r->succeeded }}</b></td>
      <td class="num"><b class="bad">{{ $r->failed }}</b></td>
      <td class="num">{{ $r->dismissed }}</td>
      <td class="num">
        @if($r->tried > 0)
          <b>{{ $r->success_rate }}%</b>
        @else
          <span style="color:var(--muted)">—</span>
        @endif
      </td>
    </tr>
  @empty
    <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:28px">{{ __('ui.No rules yet.') }}</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
