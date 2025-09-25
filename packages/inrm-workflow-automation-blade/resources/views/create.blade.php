@extends('inrm::layouts.app')
@section('content')
<div class="card">
  <h3 style="margin:0 0 .75rem 0">New Automation</h3>
  <form method="post" action="/workflow" class="grid" style="grid-template-columns:repeat(2,1fr);gap:.5rem">
    @csrf
    <input class="btn" name="name" placeholder="Name" style="grid-column:1/-1" required>
    <label><input type="checkbox" name="enabled" value="1" checked> Enabled</label>
    <select class="btn" name="trigger_type">
      <option>SCHEDULE</option>
      <option>RIM</option>
      <option>TPR</option>
      <option>INCIDENTS</option>
    </select>
    <input class="btn" type="number" min="1" name="interval_minutes" value="60" placeholder="Interval minutes (SCHEDULE)">
    <input class="btn" name="expression" placeholder="Regex (event/action filter)">
    <textarea class="btn" name="filter" placeholder='Optional JSON filter, e.g., {"tier":"critical"}' style="grid-column:1/-1"></textarea>
    <textarea class="btn" name="actions" placeholder='[{"type":"emit_rim","config":{"type":"workflow.digest"}},{"type":"snapshot_boardpack"}]' style="grid-column:1/-1" rows="4"></textarea>
    <button class="btn btn-primary" style="grid-column:1/-1">Create</button>
  </form>
</div>
@endsection
