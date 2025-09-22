<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#222; font-size: 12px; }
    .header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #444; padding-bottom:8px; margin-bottom:16px; }
    .title { font-size: 20px; font-weight: 700; }
    .muted { color:#666; font-size: 11px; }
    .kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 10px 0 20px; }
    .card { border:1px solid #ddd; border-radius:8px; padding:12px; }
    .kpi { font-size: 28px; font-weight: 700; }
    table { width:100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border:1px solid #eee; padding: 8px; text-align:left; }
    th { background:#f6f6f6; }
    .footer { border-top:1px solid #ddd; margin-top:16px; padding-top:8px; font-size: 10px; color:#888; }
  </style>
</head>
<body>
  <div class="header">
    <div class="title">Monthly Risk Board Pack</div>
    <div class="muted">Generated: {{ now()->format('Y-m-d H:i') }}</div>
  </div>

  <div class="kpi-grid">
    <div class="card"><div>Total Risks</div><div class="kpi">{{ $kpis['risks_total'] }}</div></div>
    <div class="card"><div>Active Risks</div><div class="kpi">{{ $kpis['risks_active'] }}</div></div>
    <div class="card"><div>Open Issues</div><div class="kpi">{{ $kpis['issues_open'] }}</div></div>
    <div class="card"><div>Policies Published</div><div class="kpi">{{ $kpis['policies_published'] }}</div></div>
  </div>

  <h3>Notes</h3>
  <table>
    <thead><tr><th>Area</th><th>Comment</th></tr></thead>
    <tbody>
      <tr><td>Risk Trends</td><td>Summarize changes in inherent/residual risk for top entities.</td></tr>
      <tr><td>Compliance</td><td>Coverage improvement and outstanding requirements.</td></tr>
      <tr><td>Issues</td><td>Top overdue actions and owners.</td></tr>
    </tbody>
  </table>

  <div class="footer">Confidential — For Board Use Only</div>
</body>
</html>
