<html>
  <head>
    <style>
      body{ font-family: Arial, sans-serif; font-size: 12px; }
      .kpi{ border:1px solid #ddd; border-radius:8px; padding:8px; margin-bottom:8px; }
      h1{ font-size:20px; margin-bottom:4px; }
      h3{ margin:12px 0 6px }
      table{ width:100%; border-collapse: collapse; }
      th,td{ border-bottom:1px solid #eee; padding:6px; text-align:left; }
    </style>
  </head>
  <body>
    <h1>Board Pack — {{ $dashboard->title }}</h1>
    @if($from && $to)<div><b>Period:</b> {{ $from }} → {{ $to }}</div>@endif
    <h3>Widgets</h3>
    <ul>
      @foreach(($data['resolved']??[]) as $row)
        <li>{{ $row['widget']->title }} ({{ $row['widget']->type }})</li>
      @endforeach
    </ul>
  </body>
</html>
