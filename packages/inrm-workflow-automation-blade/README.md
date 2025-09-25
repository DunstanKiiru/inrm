# INRM Workflow Automation (Blade)

Scheduler + event-driven automations for your INRM suite (Blade-first).

## Features
- **Triggers**: `SCHEDULE` (interval minutes), `RIM` (rim_events), `TPR` (tpr_rule_audit), `INCIDENTS`
- **Actions**: `emit_rim`, `create_incident`, `run_tpr`, `webhook_post`, `snapshot_boardpack`, `notify_mail` (stub)
- **Audit**: runs & logs tables, view pages
- **Auto-schedule**: runs `runDue()` every 5 minutes via service provider (or call API/Artisan)

## Install
1) Place under `packages/inrm-workflow-automation-blade`
2) Add path repo and require in app `composer.json`:
```json
"repositories": [{ "type": "path", "url": "packages/inrm-workflow-automation-blade" }],
"require": { "inrm/workflow-automation-blade": "*" }
```
3) Install & migrate:
```bash
composer update
php artisan migrate
```

## Use
- UI: `/workflow` → create automation.
- API: `POST /api/workflow/run-due` and `POST /api/workflow/run/{id}`.

### Example actions JSON
```json
[
  { "type": "emit_rim", "config": { "type": "workflow.digest", "payload": {"note":"nightly snapshot"} } },
  { "type": "snapshot_boardpack" },
  { "type": "webhook_post", "config": { "url": "https://example.com/hook", "payload": {"event":"snapshot"} } }
]
```

### Notes
- For email delivery, swap `notify_mail` to a concrete Mailable in your app.
- The provider registers a scheduler callback; if you prefer manual scheduling, disable it and add an Artisan command wrapper, or hit the API via cron.

## Demo Seeders
Seed three starter automations:
```bash
php artisan db:seed --class="Inrm\Workflow\Database\Seeders\WorkflowDemoSeeder"
```

## React Frontend
You can use the bundled React app (separate zip) to manage automations via the new API:
- `GET /api/workflow/automations`
- `GET /api/workflow/automations/{id}`
- `POST /api/workflow/automations` (JSON body)
- `POST /api/workflow/automations/{id}/toggle`
- `POST /api/workflow/automations/{id}/run`
- `POST /api/workflow/run-due`
