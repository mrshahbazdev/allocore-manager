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

<form method="get" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;margin-bottom:16px">
  <div>
    <label style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.Signal type') }}</label>
    <select name="type" style="padding:9px 12px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:13px;min-width:200px">
      <option value="">{{ __('ui.All types') }}</option>
      @foreach($types as $t => $n)
        <option value="{{ $t }}" @selected(($filters['type'] ?? '') === $t)>{{ $t }} ({{ $n }})</option>
      @endforeach
    </select>
  </div>
  <div>
    <label style="display:block;font-size:10.5px;text-transform:uppercase;letter-spacing:.09em;color:var(--faint);font-weight:600;margin-bottom:6px">{{ __('ui.Company') }}</label>
    <select name="company" style="padding:9px 12px;border:1px solid var(--line);border-radius:8px;font-family:inherit;font-size:13px;min-width:200px">
      <option value="">{{ __('ui.All companies') }}</option>
      @foreach($companies as $c)
        <option value="{{ $c }}" @selected(($filters['company'] ?? '') === $c)>{{ $c }}</option>
      @endforeach
    </select>
  </div>
  <button type="submit">{{ __('ui.Filter') }}</button>
  @if(array_filter($filters))<a href="{{ route('signals') }}" class="chip" style="text-decoration:none">{{ __('ui.Reset') }}</a>@endif
</form>

@if($signals->count())
  <table>
    <tr><th>{{ __('ui.Signal type') }}</th><th>{{ __('ui.Company') }}</th><th>{{ __('ui.Source') }}</th><th>{{ __('ui.Occurred') }}</th></tr>
    @foreach($signals as $s)
      <tr>
        <td>
          <details>
            <summary style="cursor:pointer"><span class="chip">{{ $s->type }}</span></summary>
            @php $fields = collect($s->payload ?? [])->filter(fn($v) => !is_array($v) && $v !== null); @endphp
            @if($fields->count())
              <div style="margin-top:8px;display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;max-width:520px">
                @foreach($fields as $k => $v)
                  <div style="padding:9px 12px;border:1px solid var(--line);border-radius:9px;background:#fafbfc">
                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--faint);font-weight:600;margin-bottom:3px">{{ str_replace('_', ' ', $k) }}</div>
                    <div style="font-size:13px;font-weight:600;word-break:break-word">{{ is_bool($v) ? ($v ? __('ui.Yes') : __('ui.No')) : $v }}</div>
                  </div>
                @endforeach
              </div>
            @else
              <div style="margin-top:8px;color:var(--faint);font-size:12px">—</div>
            @endif
          </details>
        </td>
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
