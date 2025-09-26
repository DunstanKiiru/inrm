<?php
namespace App\Http\Controllers\Api;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RulesRunController extends Controller
{
    public function run(Request $r)
    {
        $vendorId = $r->input('vendor_id'); // optional
        $now = now();
        $rules = DB::table('tpr_rules')->where('enabled',1)->orderBy('id')->get()->all();
        $summary = ['evaluated'=>count($rules),'triggers'=>0,'issues'=>0,'audits'=>0,'skipped'=>0];
        foreach ($rules as $rule) {
            $scope = json_decode($rule->scope ?? 'null', true) ?: [];
            $vendorsQ = DB::table('tpr_vendors')->select('id','code','name','tier','category','status');
            if (!empty($scope['vendor_id'])) $vendorsQ->where('id',$scope['vendor_id']);
            if (!empty($scope['tier'])) $vendorsQ->where('tier',$scope['tier']);
            if (!empty($scope['category'])) $vendorsQ->where('category',$scope['category']);
            if ($vendorId) $vendorsQ->where('id',$vendorId);
            $vendors = $vendorsQ->get()->all();

            foreach ($vendors as $v) {
                $winStart = $now->clone()->subDays((int)$rule->window_days)->toDateString();
                $winEnd = $now->toDateString();
                $matched = [];

                if ($rule->type === 'KRI_ALERTS_IN_WINDOW' || $rule->metric === 'kri') {
                    $q = DB::table('tpr_vendor_kri_measures')->where('vendor_id',$v->id)
                        ->where('measured_at','>=',$winStart)->whereIn('status',['alert','breach']);
                    if ($rule->code_pattern) $q->where('kri_code','REGEXP',$rule->code_pattern);
                    $rows = $q->select('kri_code', DB::raw('COUNT(*) as n'))->groupBy('kri_code')->get()->all();
                    foreach ($rows as $row) if ((int)$row->n >= (int)$rule->threshold) $matched[] = ['metric'=>'kri','code'=>$row->kri_code,'count'=>(int)$row->n];
                }

                if ($rule->type === 'SLA_BREACHES_IN_WINDOW' || $rule->metric === 'sla') {
                    $q = DB::table('tpr_vendor_sla_measures')->where('vendor_id',$v->id)
                        ->where('measured_at','>=',$winStart)->where('status','breach');
                    if ($rule->code_pattern) $q->where('sla_code','REGEXP',$rule->code_pattern);
                    $rows = $q->select('sla_code', DB::raw('COUNT(*) as n'))->groupBy('sla_code')->get()->all();
                    foreach ($rows as $row) if ((int)$row->n >= (int)$rule->threshold) $matched[] = ['metric'=>'sla','code'=>$row->sla_code,'count'=>(int)$row->n];
                }

                foreach ($matched as $m) {
                    $summary['triggers']++;
                    // Dedup: if an audit exists for this (rule,vendor,code,window_end), skip creating another issue
                    $exists = DB::table('tpr_rule_audit')->where([
                        'rule_id'=>$rule->id,'vendor_id'=>$v->id,'matched_code'=>$m['code'],'window_end'=>$winEnd
                    ])->exists();
                    if ($exists) { $summary['skipped']++; continue; }

                    $actionTaken = 'noop'; $issueId = null;
                    if ($rule->action === 'create_issue' && \Illuminate\Support\Facades\Schema::hasTable('issues')) {
                        $title = $this->renderTpl($rule->title_template ?? '[TPR] {{metric}} threshold for {{vendor_code}}', [
                            'metric'=>$m['metric'],'vendor_code'=>$v->code,'vendor_name'=>$v->name,'matched_code'=>$m['code'],'count'=>$m['count'],
                            'window_days'=>$rule->window_days,'rule_id'=>$rule->id
                        ]);
                        $desc = $this->renderTpl($rule->description_template ?? 'Rule {{rule_id}} triggered for {{vendor_code}}: {{count}} in {{window_days}} days ({{metric}}={{matched_code}}).', [
                            'metric'=>$m['metric'],'vendor_code'=>$v->code,'vendor_name'=>$v->name,'matched_code'=>$m['code'],'count'=>$m['count'],
                            'window_days'=>$rule->window_days,'rule_id'=>$rule->id
                        ]);
                        $issueId = DB::table('issues')->insertGetId([
                            'title'=> $title,
                            'description'=> $desc,
                            'priority'=> $rule->issue_priority ?? 'high',
                            'status'=> 'open',
                            'created_at'=> now(), 'updated_at'=> now()
                        ]);
                        $summary['issues']++; $actionTaken = 'created_issue';
                    } elseif ($rule->action === 'rim_event_only' && \Illuminate\Support\Facades\Schema::hasTable('rim_events')) {
                        DB::table('rim_events')->insert([
                            'type'=>'TPR_RULE_TRIGGER','severity'=>'medium','risk_id'=>null,
                            'message'=>"TPR rule trigger vendor={$v->code} metric={$m['metric']} code={$m['code']} count={$m['count']}",
                            'occurred_at'=> now(), 'metrics'=> json_encode(['rule_id'=>$rule->id,'vendor_id'=>$v->id,'metric'=>$m['metric'],'code'=>$m['code'],'count'=>$m['count']]),
                            'created_at'=> now(), 'updated_at'=> now()
                        ]);
                        $actionTaken = 'rim_event';
                    }

                    DB::table('tpr_rule_audit')->insert([
                        'rule_id'=>$rule->id,
                        'vendor_id'=>$v->id,'vendor_code'=>$v->code,
                        'metric'=>$m['metric'],'matched_code'=>$m['code'],'count'=>$m['count'],
                        'window_start'=>$winStart,'window_end'=>$winEnd,
                        'action_taken'=>$actionTaken,'issue_id'=>$issueId,
                        'payload'=> json_encode(['vendor'=>$v,'rule'=>$rule,'match'=>$m]),
                        'triggered_at'=> now(),'created_at'=> now(),'updated_at'=> now()
                    ]);
                    $summary['audits']++;
                }
            }
        }
        return $summary;
    }

    protected function renderTpl(string $tpl, array $ctx): string
    {
        foreach ($ctx as $k=>$v) { $tpl = str_replace('{{'+$k+'}}', (string)$v, $tpl); }
        // Fallback simple replacements to handle {{key}} safely
        foreach ($ctx as $k=>$v) { $tpl = preg_replace('/{{\s*'+preg_quote($k,'/')+'\s*}}/', (string)$v, $tpl); }
        return $tpl;
    }
}
