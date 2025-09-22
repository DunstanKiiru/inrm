<html><body>
<h1>Monthly Risk Board Pack</h1>
<p>Generated: {{ now() }}</p>
<ul>
  <li>Total Risks: {{ $kpis['risks_total'] }}</li>
  <li>Active Risks: {{ $kpis['risks_active'] }}</li>
  <li>Open Issues: {{ $kpis['issues_open'] }}</li>
  <li>Policies Published: {{ $kpis['policies_published'] }}</li>
</ul>
</body></html>