<?php
namespace Inrm\Workflow\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Engine
{
    public function runDue(): array
    {
        $now = now();
        $list = DB::table('workflow_automations')->where('enabled',1)->get();
        $results = [];
        foreach ($list as $auto) {
            if ($auto->trigger_type === 'SCHEDULE') {
                $last = $auto->last_run_at ? now()->parse($auto->last_run_at) : null;
                $interval = max(1, (int)$auto->interval_minutes);
                if (!$last || $last->diffInMinutes($now) >= $interval) {
                    $results[] = $this->runAutomation($auto->id);
                }
            } else {
                // Event-driven: poll since event_since_at (default 1 day)
                $since = $auto->event_since_at ? now()->parse($auto->event_since_at) : now()->subDay();
                $expr = $auto->expression ?: '.*';
                $matched = [];
                if ($auto->trigger_type === 'RIM') {
                    $rim = DB::table('rim_events')->where('created_at','>=',$since)->orderBy('created_at')->get();
                    foreach ($rim as $e) {
                        if (@preg_match('/'.str_replace('/','\/',$expr).'/i', $e->type)===1) $matched[] = $e;
                    }
                } elseif ($auto->trigger_type === 'TPR') {
                    $tpr = DB::table('tpr_rule_audit')->where('triggered_at','>=',$since)->orderBy('triggered_at')->get();
                    foreach ($tpr as $e) {
                        $code = $e->action_taken ?? '';
                        if (@preg_match('/'.str_replace('/','\/',$expr).'/i', $code)===1) $matched[] = $e;
                    }
                } elseif ($auto->trigger_type === 'INCIDENTS') {
                    $inc = DB::table('incidents')->where('updated_at','>=',$since)->orderBy('updated_at')->get();
                    foreach ($inc as $i) {
                        $code = $i->status.'::'.$i->severity;
                        if (@preg_match('/'.str_replace('/','\/',$expr).'/i', $code)===1) $matched[] = $i;
                    }
                }
                if (!empty($matched)) {
                    $results[] = $this->runAutomation($auto->id, ['matched'=>count($matched)]);
                    DB::table('workflow_automations')->where('id',$auto->id)->update(['event_since_at'=>$now,'updated_at'=>$now]);
                }
            }
        }
        return ['ran'=>count($results),'results'=>$results];
    }

    public function runAutomation(int $automationId, array $meta = []): array
    {
        $now = now();
        $auto = DB::table('workflow_automations')->where('id',$automationId)->first();
        if (!$auto || !$auto->enabled) return ['id'=>$automationId,'status'=>'skipped'];
        $runId = DB::table('workflow_runs')->insertGetId([
            'automation_id'=>$automationId,'started_at'=>$now,'status'=>'running','meta'=>json_encode($meta),
            'created_at'=>$now,'updated_at'=>$now
        ]);
        $ok = true; $actionsRun=0; $messages=[];
        $actions = DB::table('workflow_actions')->where('automation_id',$automationId)->orderBy('order')->get();
        foreach ($actions as $a) {
            try {
                $messages[] = $this->executeAction($a, $auto, $runId);
                $actionsRun++;
            } catch (\Throwable $ex) {
                $ok=false;
                $this->log($runId, 'error', 'Action failed: '.$a->type, ['exception'=>['msg'=>$ex->getMessage(),'line'=>$ex->getLine(),'file'=>$ex->getFile()]]);
                break;
            }
        }
        DB::table('workflow_runs')->where('id',$runId)->update([
            'status'=>$ok ? 'success':'failed','finished_at'=>now(),'updated_at'=>now()
        ]);
        DB::table('workflow_automations')->where('id',$automationId)->update(['last_run_at'=>now(),'updated_at'=>now()]);
        return ['id'=>$automationId,'run_id'=>$runId,'status'=>$ok?'success':'failed','actions'=>$actionsRun,'messages'=>$messages];
    }

    protected function executeAction($action, $auto, int $runId): string
    {
        $cfg = $action->config ? json_decode($action->config, true) : [];
        switch (strtolower($action->type)) {
            case 'webhook_post':
                $url = $cfg['url'] ?? null;
                if (!$url) { $this->log($runId,'warn','webhook_post: no URL'); return 'webhook_post: skipped'; }
                try {
                    $resp = \Illuminate\Support\Facades\Http::timeout(5)->post($url, $cfg['payload'] ?? ['automation'=>$auto->name,'run_id'=>$runId]);
                    $this->log($runId, 'info', 'webhook_post', ['status'=>$resp->status()]);
                } catch (\Throwable $e) {
                    $this->log($runId, 'error', 'webhook_post failed', ['error'=>$e->getMessage()]);
                    throw $e;
                }
                return 'webhook_post: ok';
            case 'create_incident':
                $title = $cfg['title'] ?? ('Automation: '.$auto->name);
                $sev = $cfg['severity'] ?? 'medium';
                DB::table('incidents')->insert([
                    'title'=>$title,'severity'=>$sev,'status'=>'open','description'=>$cfg['description'] ?? null,
                    'created_at'=>now(),'updated_at'=>now(),'closed_at'=>null
                ]);
                $this->log($runId, 'info', 'incident.created', ['title'=>$title,'severity'=>$sev]);
                return 'create_incident: ok';
            case 'emit_rim':
                $type = $cfg['type'] ?? 'workflow.event';
                $payload = $cfg['payload'] ?? ['automation'=>$auto->name];
                DB::table('rim_events')->insert(['type'=>$type,'payload'=>json_encode($payload),'created_at'=>now(),'updated_at'=>now()]);
                $this->log($runId, 'info', 'rim_event', ['type'=>$type]);
                return 'emit_rim: ok';
            case 'run_tpr':
                // Trigger TPR evaluator via internal API route if available
                try {
                    app()->call('\Inrm\TPRBlade\Http\Controllers\Api\RulesRunController@run', ['r'=>request()]);
                    $this->log($runId,'info','tpr.run',[]);
                } catch (\Throwable $e) {
                    $this->log($runId,'error','tpr.run failed',['error'=>$e->getMessage()]); throw $e;
                }
                return 'run_tpr: ok';
            case 'snapshot_boardpack':
                $snap = $this->boardpackSnapshot();
                DB::table('workflow_logs')->insert(['run_id'=>$runId,'level'=>'info','message'=>'boardpack.snapshot','context'=>json_encode(['counts'=>['risks'=>count($snap['top_risks']),'events'=>count($snap['rim_events'])]]),'created_at'=>now(),'updated_at'=>now()]);
                return 'snapshot_boardpack: ok';
            case 'notify_mail':
                // Simple stub: log only (mail wiring varies per app)
                $to = $cfg['to'] ?? null;
                $this->log($runId,'info','notify_mail (stub)', ['to'=>$to]);
                return 'notify_mail: stub';
            default:
                $this->log($runId,'warn','unknown action', ['type'=>$action->type]);
                return 'unknown: skipped';
        }
    }

    protected function boardpackSnapshot(): array
    {
        $topRisks = DB::table('risks')->orderByRaw('(residual_likelihood*residual_impact) desc')->limit(5)->get(['id','title','residual_likelihood','residual_impact'])->toArray();
        $rim = DB::table('rim_events')->orderByDesc('created_at')->limit(10)->get()->toArray();
        $tpr = DB::table('tpr_rule_audit')->orderByDesc('triggered_at')->limit(10)->get()->toArray();
        return ['top_risks'=>$topRisks,'rim_events'=>$rim,'tpr_events'=>$tpr];
    }

    protected function log(int $runId, string $level, string $message, array $ctx=[]): void
    {
        DB::table('workflow_logs')->insert(['run_id'=>$runId,'level'=>$level,'message'=>$message,'context'=>json_encode($ctx),'created_at'=>now(),'updated_at'=>now()]);
    }
}
