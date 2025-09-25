@extends('inrm::layouts.app')
@section('content')
<div class="grid" style="grid-template-columns:1fr 1fr; gap:1rem">
  <div class="card">
    <h3 style="margin:0">{{ $a->name }}</h3>
    <div class="muted">Trigger: {{ $a->trigger_type }} @if($a->expression) ({{ $a->expression }}) @endif • Interval: {{ $a->interval_minutes }}m</div>
    <div class="muted">Enabled: {{ $a->enabled ? 'yes' : 'no' }} • Last run: {{ $a->last_run_at ?? '—' }}</div>
    <div style="margin-top:.5rem">
      <form method="post" action="/workflow/{{ $a->id }}/run" style="display:inline">@csrf <button class="btn btn-primary">Run Now</button></form>
      @if ($a->enabled)
        <form method="post" action="/workflow/{{ $a->id }}/disable" style="display:inline">@csrf <button class="btn">Disable</button></form>
      @else
        <form method="post" action="/workflow/{{ $a->id }}/enable" style="display:inline">@csrf <button class="btn">Enable</button></form>
      @endif
    </div>
  </div>
  <div class="card">
    <h4 style="margin:.2rem 0">Actions</h4>
    <ul>
      @foreach ($actions as $act) <li><span class="badge">{{ $act->type }}</span> — {{ \Illuminate\Support\Str::limit(json_encode($act->config), 80) }}</li> @endforeach
    </ul>
  </div>
  <div class="card" style="grid-column:1/-1">
    <h4 style="margin:.2rem 0">Recent Runs</h4>
    <table><thead><tr><th>When</th><th>Status</th><th>Meta</th></tr></thead>
      <tbody>@foreach ($runs as $r) <tr><td>{{ $r->started_at }}</td><td>{{ $r->status }}</td><td>{{ $r->meta }}</td></tr>@endforeach</tbody>
    </table>
  </div>
</div>
@endsection
