<div class="card {{ $rec->status !== 'open' ? 'done' : '' }}">
  <div class="top">
    <div class="t">{{ $rec->localizedTitle() }}</div>
    @if($rec->status === 'open')
      <span class="sev {{ $rec->severity }}"><i></i>{{ __('ui.'.$rec->severity) }}</span>
    @else
      <span class="sev neutral"><i></i>{{ __('ui.'.($rec->latestOutcome?->result ?? $rec->status)) }}</span>
    @endif
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
  @if($rec->status === 'open')
  <div class="actions">
    <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="success"><button class="yes" type="submit">{{ __('ui.Done — it worked') }}</button></form>
    <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="failed"><button type="submit">{{ __("ui.Didn't work") }}</button></form>
    <form method="post" action="{{ route('outcome', $rec) }}">@csrf<input type="hidden" name="result" value="dismissed"><button type="submit">{{ __('ui.Not relevant') }}</button></form>
  </div>
  @endif
</div>
