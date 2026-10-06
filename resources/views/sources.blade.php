@extends('layout')
@section('page','sources')
@section('title', __('ui.Sources'))
@section('content')
<div class="pagehead">
  <div>
    <h1>{{ __('ui.Sources') }}</h1>
    <p>{{ __('ui.sources_sub') }}</p>
  </div>
</div>

@if($errors->any())<div class="note">{{ $errors->first() }}</div>@endif

<form method="post" action="{{ route('sources.store') }}" class="card" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
  @csrf
  <div style="flex:1;min-width:220px">
    <label style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.New source') }}</label>
    <input name="name" required placeholder="{{ __('ui.Source name placeholder') }}" style="width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:13.5px">
  </div>
  <button type="submit" class="yes">{{ __('ui.Create source') }}</button>
</form>

<table>
  <thead><tr>
    <th>{{ __('ui.Name') }}</th><th>{{ __('ui.Token') }}</th><th>{{ __('ui.Ingest URL') }}</th><th class="num">{{ __('ui.Signals') }}</th><th>{{ __('ui.Last signal') }}</th><th></th>
  </tr></thead>
  <tbody>
  @forelse($sources as $s)
    <tr>
      <td><b>{{ $s->name }}</b></td>
      <td><span class="chip" role="button" style="cursor:pointer" title="{{ __('ui.Copy') }}" onclick="navigator.clipboard.writeText('{{ $s->token }}')">{{ \Illuminate\Support\Str::limit($s->token, 12, '…') }}</span></td>
      <td><span class="chip" role="button" style="cursor:pointer" title="{{ __('ui.Copy') }}" onclick="navigator.clipboard.writeText('{{ rtrim(config('app.url'), '/').'/ingest/'.$s->token }}')">POST /ingest/{{ \Illuminate\Support\Str::limit($s->token, 10, '…') }}</span></td>
      <td class="num">{{ $s->signals_count }}</td>
      <td>{{ $s->signals_max_occurred_at ? \Illuminate\Support\Carbon::parse($s->signals_max_occurred_at)->format('d.m.Y H:i') : '—' }}</td>
      <td style="text-align:right">
        <form method="post" action="{{ route('sources.destroy', $s) }}" onsubmit="return confirm('{{ __('ui.source_delete_confirm') }}')">
          @csrf @method('DELETE')
          <button type="submit">{{ __('ui.Delete') }}</button>
        </form>
      </td>
    </tr>
  @empty
    <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:28px">{{ __('ui.No sources yet.') }}</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
