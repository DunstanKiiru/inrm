<html>
  <body style="font-family: Arial, sans-serif">
    <h2>{{ $dashboard->title }} — Daily Digest</h2>
    <p>Hello, here are your key metrics.</p>
    <ul>
      @foreach($dashboard->widgets as $w)
        <li><b>{{ $w->title }}</b> — type: {{ $w->type }}</li>
      @endforeach
    </ul>
    <p style="color:#888">Automated message.</p>
  </body>
</html>
