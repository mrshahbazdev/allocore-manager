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
  <form method="post" action="{{ route('outcome', $rec) }}">
    @csrf
    <input type="text" name="note" placeholder="{{ __('ui.Note (optional)') }}" style="width:100%;padding:8px 11px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:12.5px;margin-bottom:8px">
    <div class="actions">
      <button class="yes" type="submit" name="result" value="success">{{ __('ui.Done — it worked') }}</button>
      <button type="submit" name="result" value="failed">{{ __("ui.Didn't work") }}</button>
      <button type="submit" name="result" value="dismissed">{{ __('ui.Not relevant') }}</button>
    </div>
  </form>
  @else
    @if($rec->latestOutcome?->note)
      <div class="meta"><span><b>{{ __('ui.Note') }}</b> · {{ $rec->latestOutcome->note }}</span></div>
    @endif
    <form method="post" action="{{ route('recommendations.reopen', $rec) }}" style="margin-top:8px">
      @csrf
      <button type="submit" style="padding:6px 12px;border:1px solid var(--line);border-radius:8px;background:transparent;font-family:inherit;font-size:12px;cursor:pointer;color:var(--muted)">{{ __('ui.Reopen') }}</button>
    </form>
  @endif
</div>
