<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RulesRunController extends Controller
{
    public function run(Request $r)
    {
        $vendorId = $r->input('vendor_id'); // optional
        $now = now();

        $rules = DB::table('tpr_rules')
            ->where('enabled', 1)
            ->orderBy('id')
            ->get()
            ->all();

        $summary = [
            'evaluated'             => count($rules),
            'triggers'              => 0,
            'issues'                => 0,
            'audits'                => 0,
            'skipped'               => 0,
            'suppressed'            => 0,
            'autoclosed'            => 0,
            'reopened'              => 0,
            'escalations'           => 0,
            'cooloff_skips'         => 0,
            'cooloff_logs'          => 0,
            'once_skips'            => 0,
            'reopen_cooldown_skips' => 0,
        ];

        // --- Load suppressions ---
        $suppressions = DB::table('tpr_rule_suppressions')
            ->where('until', '>=', $now->toDateString())
            ->get()
            ->all();

        foreach ($rules as $rule) {
            $scope = json_decode($rule->scope ?? 'null', true) ?: [];

            $vendorsQ = DB::table('tpr_vendors')->select('id','code','name','tier','category','status');
            if (!empty($scope['vendor_id'])) $vendorsQ->where('id', $scope['vendor_id']);
            if (!empty($scope['tier'])) $vendorsQ->where('tier', $scope['tier']);
            if (!empty($scope['category'])) $vendorsQ->where('category', $scope['category']);
            if ($vendorId) $vendorsQ->where('id', $vendorId);
            $vendors = $vendorsQ->get()->all();

            foreach ($vendors as $v) {
                // --- Suppression check ---
                $isSuppressed = false;
                foreach ($suppressions as $s) {
                    if (
                        ($s->rule_id === null || (int)$s->rule_id === (int)$rule->id) &&
                        ($s->vendor_id === null || (int)$s->vendor_id === (int)$v->id)
                    ) {
                        $isSuppressed = true;
                        break;
                    }
                }

                if ($isSuppressed) {
                    $this->audit($rule, $v, null, null, 0, 'suppressed', null, null);
                    $summary['suppressed']++;
                    continue;
                }

                $winStart = $now->clone()->subDays((int)$rule->window_days)->toDateString();

                // --- Composite rule evaluation ---
                if (($rule->logic_type ?? 'SIMPLE') === 'COMPOSITE' && $rule->expression) {
                    if ($this->evalComposite($v->id, $rule, $winStart)) {
                        $this->processTrigger($rule, $v, 'composite', 'COMPOSITE', 1, $winStart, $now->toDateString(), $summary);
                    }
                    $this->autoCloseIfCleared($rule, $v, 'composite', 'COMPOSITE', $winStart, $summary);
                    continue;
                }

                // --- Simple KRI/SLA rule evaluation ---
                $pairs = [];

                if ($rule->type === 'KRI_ALERTS_IN_WINDOW' || $rule->metric === 'kri') {
                    $rows = DB::table('tpr_vendor_kri_measures')->where('vendor_id',$v->id)
                        ->where('measured_at','>=',$winStart)->whereIn('status',['alert','breach'])
                        ->when($rule->code_pattern, fn($q)=>$q->where('kri_code','REGEXP',$rule->code_pattern))
                        ->select('kri_code as code', DB::raw('COUNT(*) as n'))->groupBy('kri_code')->get()->all();
                    foreach ($rows as $row) {
                        if ((int)$row->n >= (int)$rule->threshold) {
                            $pairs[] = ['metric'=>'kri','code'=>$row->code,'count'=>(int)$row->n];
                        }
                    }
                }

                if ($rule->type === 'SLA_BREACHES_IN_WINDOW' || $rule->metric === 'sla') {
                    $rows = DB::table('tpr_vendor_sla_measures')->where('vendor_id',$v->id)
                        ->where('measured_at','>=',$winStart)->where('status','breach')
                        ->when($rule->code_pattern, fn($q)=>$q->where('sla_code','REGEXP',$rule->code_pattern))
                        ->select('sla_code as code', DB::raw('COUNT(*) as n'))->groupBy('sla_code')->get()->all();
                    foreach ($rows as $row) {
                        if ((int)$row->n >= (int)$rule->threshold) {
                            $pairs[] = ['metric'=>'sla','code'=>$row->code,'count'=>(int)$row->n];
                        }
                    }
                }

                foreach ($pairs as $p) {
                    $this->processTrigger($rule, $v, $p['metric'], $p['code'], (int)$p['count'], $winStart, $now->toDateString(), $summary);
                }
                foreach ($pairs as $p) {
                    $this->autoCloseIfCleared($rule, $v, $p['metric'], $p['code'], $winStart, $summary);
                }
            }
        }

        return $summary;
    }

    protected function processTrigger($rule, $v, string $metric, string $code, int $count, string $winStart, string $winEnd, array &$summary)
    {
        $now = now();

        // last audit for cooldown / once-off checks
        $lastAudit = DB::table('tpr_rule_audit')
            ->where('rule_id',$rule->id)->where('vendor_id',$v->id)
            ->where('metric',$metric)->where('matched_code',$code)
            ->orderByDesc('triggered_at')->first();

        // once strategy
        if (($rule->cool_off_strategy ?? '') === 'once' && $lastAudit) {
            $summary['once_skips']++;
            $this->audit($rule, $v, $metric, $code, $count, 'skip_once', null, null);
            return;
        }

        // cooldown
        if ($lastAudit && $rule->cool_off_days && $lastAudit->triggered_at >= $now->clone()->subDays($rule->cool_off_days)) {
            $summary['cooloff_skips']++;
            $this->audit($rule, $v, $metric, $code, $count, 'skip_cooloff', null, null);
            return;
        }

        // escalation
        $levels = json_decode($rule->escalation_levels ?? '[]', true);
        $chainId = null;
        if ($levels) {
            $triggers = DB::table('tpr_rule_audit')->where('rule_id',$rule->id)->where('vendor_id',$v->id)
                ->where('metric',$metric)->where('matched_code',$code)
                ->where('triggered_at','>=',$now->clone()->subDays($rule->window_days))->count();
            foreach ($levels as $lvl=>$need) {
                if ($triggers >= $need) {
                    $summary['escalations']++;
                    $this->audit($rule,$v,$metric,$code,$count,'escalated',$lastAudit->issue_id??null,$lastAudit->chain_id??null);
                }
            }
        }

        // maybe create issue
        $issueId = $this->maybeCreateIssue($rule,$v,$metric,$code,$count);
        if ($issueId) $summary['issues']++;

        // audit log
        $this->audit($rule,$v,$metric,$code,$count,'triggered',$issueId,$chainId);
        $summary['triggers']++;
    }

    protected function maybeCreateIssue($rule, $v, string $metric, string $code, int $count): ?int
    {
        if (($rule->action ?? 'create_issue') !== 'create_issue') return null;

        $now = now();

        $openIssue = DB::table('tpr_issues')
            ->where('vendor_id',$v->id)->where('rule_id',$rule->id)
            ->where('metric',$metric)->where('matched_code',$code)
            ->whereNull('closed_at')->first();

        if ($openIssue) {
            if ($rule->auto_reopen && $this->isIssueClosed($openIssue->id)) {
                if ($rule->reopen_cooldown_hours) {
                    $lastAudit = DB::table('tpr_rule_audit')->where('issue_id',$openIssue->id)
                        ->orderByDesc('triggered_at')->first();
                    if ($lastAudit && $lastAudit->triggered_at >= $now->clone()->subHours($rule->reopen_cooldown_hours)) {
                        return null; // skip reopen
                    }
                }
                $this->reopenIssue($openIssue->id);
                return $openIssue->id;
            }
            return $openIssue->id;
        }

        $id = DB::table('tpr_issues')->insertGetId([
            'vendor_id'=>$v->id,'rule_id'=>$rule->id,'metric'=>$metric,'matched_code'=>$code,
            'status'=>'open','priority'=>$rule->issue_priority ?? 'high',
            'title'=>$this->renderTpl($rule->title_template ?? '[TPR] {{metric}} threshold for {{vendor_code}}',
                ['vendor_code'=>$v->code,'vendor_name'=>$v->name,'rule_id'=>$rule->id,'metric'=>$metric,'matched_code'=>$code,'count'=>$count,'window_days'=>$rule->window_days]),
            'description'=>$this->renderTpl($rule->description_template ??
                'Rule {{rule_id}} triggered for {{vendor_code}}: {{count}} in {{window_days}} days ({{metric}}={{matched_code}}).',
                ['vendor_code'=>$v->code,'vendor_name'=>$v->name,'rule_id'=>$rule->id,'metric'=>$metric,'matched_code'=>$code,'count'=>$count,'window_days'=>$rule->window_days]),
            'created_at'=>$now,'updated_at'=>$now
        ]);
        return $id;
    }

    protected function isIssueClosed(int $issueId): bool
    {
        $row = DB::table('tpr_issues')->where('id',$issueId)->first();
        return $row && $row->status === 'closed';
    }

    protected function reopenIssue(int $issueId): void
    {
        DB::table('tpr_issues')->where('id',$issueId)->update([
            'status'=>'open','closed_at'=>null,'updated_at'=>now()
        ]);
    }

    protected function autoCloseIfCleared($rule, $v, string $metric, string $code, string $since, array &$summary): void
    {
        if (!$rule->auto_close_days) return;
        $thresholdDate = now()->subDays($rule->auto_close_days);

        $openIssues = DB::table('tpr_issues')->where('vendor_id',$v->id)
            ->where('rule_id',$rule->id)->where('metric',$metric)->where('matched_code',$code)
            ->where('status','open')->get()->all();

        foreach ($openIssues as $issue) {
            $lastTrig = DB::table('tpr_rule_audit')->where('issue_id',$issue->id)
                ->orderByDesc('triggered_at')->first();
            if (!$lastTrig || $lastTrig->triggered_at < $thresholdDate) {
                DB::table('tpr_issues')->where('id',$issue->id)->update([
                    'status'=>'closed','closed_at'=>now(),'updated_at'=>now()
                ]);
                $this->audit($rule,$v,$metric,$code,0,'auto_closed',$issue->id,null);
                $summary['autoclosed']++;
            }
        }
    }

    protected function audit($rule, $v, ?string $metric, ?string $code, int $count, string $action, ?int $issueId, ?int $chainId): void
    {
        DB::table('tpr_rule_audit')->insert([
            'rule_id'=>$rule->id,'vendor_id'=>$v->id,'metric'=>$metric,'matched_code'=>$code,
            'count'=>$count,'action'=>$action,'issue_id'=>$issueId,'chain_id'=>$chainId,
            'triggered_at'=>now(),'created_at'=>now(),'updated_at'=>now()
        ]);
    }

    protected function renderTpl(string $tpl, array $ctx): string
    {
        return preg_replace_callback('/{{(\w+)}}/', fn($m)=>$ctx[$m[1]]??$m[0], $tpl);
    }

    protected function evalComposite(int $vendorId, $rule, string $since): bool
    {
        if (!$rule->expression) return false;
        $expr = $rule->expression;

        // Replace references like rule:123 with boolean of that rule triggered
        $expr = preg_replace_callback('/rule:(\d+)/', function($m) use ($vendorId,$since){
            $rid = (int)$m[1];
            $cnt = DB::table('tpr_rule_audit')->where('rule_id',$rid)->where('vendor_id',$vendorId)
                ->where('triggered_at','>=',$since)->count();
            return $cnt>0 ? 'true' : 'false';
        }, $expr);

        // simple eval
        $expr = str_ireplace(['AND','OR','NOT'],['&&','||','!'],$expr);
        try {
            return eval("return ($expr);");
        } catch (\Throwable $e) {
            return false;
        }
    }
}
