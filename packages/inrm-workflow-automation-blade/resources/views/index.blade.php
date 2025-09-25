@extends('inrm::layouts.app')
@section('content')
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h3 style="margin:0">Workflow Automations</h3>
    <a class="btn btn-primary" href="/workflow/create">New Automation</a>
  </div>
</div>
<div class="card">
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Trigger</th><th>Interval</th><th>Enabled</th><th>Last Run</th><th></th></tr></thead>
    <tbody>
      @foreach ($rows as $a)
      <tr>
        <td>#{{ $a->id }}</td>
        <td>{{ $a->name }}</td>
        <td>{{ $a->trigger_type }} {{ $a->expression ? '(' . $a->expression . ')' : '' }}</td>
        <td>{{ $a->interval_minutes }}m</td>
        <td>{{ $a->enabled ? 'yes' : 'no' }}</td>
        <td>{{ $a->last_run_at ?? '—' }}</td>
        <td>
          <form method="post" action="/workflow/{{ $a->id }}/run" style="display:inline">@csrf <button class="btn">Run</button></form>
          @if ($a->enabled)
            <form method="post" action="/workflow/{{ $a->id }}/disable" style="display:inline">@csrf <button class="btn">Disable</button></form>
          @else
            <form method="post" action="/workflow/{{ $a->id }}/enable" style="display:inline">@csrf <button class="btn">Enable</button></form>
          @endif
          <a class="btn" href="/workflow/{{ $a->id }}">Open</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div style="margin-top:.75rem">{{ $rows->links() }}</div>
</div>
@endsection
