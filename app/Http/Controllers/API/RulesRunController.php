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
            'evaluated'     => count($rules),
            'triggers'      => 0,
            'issues'        => 0,
            'audits'        => 0,
            'skipped'       => 0,
            'suppressed'    => 0,
            'autoclosed'    => 0,
            'reopened'      => 0,
            'cooloff_skips' => 0,
            'cooloff_logs'  => 0,
            'once_skips'    => 0,
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
                $winEnd   = $now->toDateString();

                // --- Composite rule evaluation ---
                if (($rule->logic_type ?? 'SIMPLE') === 'COMPOSITE' && $rule->expression) {
                    $passed = $this->evalComposite($v->id, $rule, $winStart);
                    if ($passed) {
                        $this->processTrigger($rule, $v, 'composite', 'COMPOSITE', 1, $winStart, $winEnd, $summary);
                    }
                    $this->autoCloseIfCleared($rule, $v, 'composite', 'COMPOSITE', $winStart, $summary);
                    continue;
                }

                // --- Simple KRI/SLA rule evaluation ---
                $pairs = [];

                if ($rule->type === 'KRI_ALERTS_IN_WINDOW' || $rule->metric === 'kri') {
                    $rows = DB::table('tpr_vendor_kri_measures')
                        ->where('vendor_id', $v->id)
                        ->where('measured_at', '>=', $winStart)
                        ->whereIn('status', ['alert','breach'])
                        ->when($rule->code_pattern, fn($q) => $q->where('kri_code','REGEXP',$rule->code_pattern))
                        ->select('kri_code as code', DB::raw('COUNT(*) as n'))
                        ->groupBy('kri_code')
                        ->get()->all();

                    foreach ($rows as $row) {
                        if ((int)$row->n >= (int)$rule->threshold) {
                            $pairs[] = ['metric'=>'kri','code'=>$row->code,'count'=>(int)$row->n];
                        }
                    }
                }

                if ($rule->type === 'SLA_BREACHES_IN_WINDOW' || $rule->metric === 'sla') {
                    $rows = DB::table('tpr_vendor_sla_measures')
                        ->where('vendor_id', $v->id)
                        ->where('measured_at','>=',$winStart)
                        ->where('status','breach')
                        ->when($rule->code_pattern, fn($q) => $q->where('sla_code','REGEXP',$rule->code_pattern))
                        ->select('sla_code as code', DB::raw('COUNT(*) as n'))
                        ->groupBy('sla_code')
                        ->get()->all();

                    foreach ($rows as $row) {
                        if ((int)$row->n >= (int)$rule->threshold) {
                            $pairs[] = ['metric'=>'sla','code'=>$row->code,'count'=>(int)$row->n];
                        }
                    }
                }

                foreach ($pairs as $p) {
                    $this->processTrigger($rule, $v, $p['metric'], $p['code'], $p['count'], $winStart, $winEnd, $summary);
                }

                foreach ($pairs as $p) {
                    $this->autoCloseIfCleared($rule, $v, $p['metric'], $p['code'], $winStart, $summary);
                }
            }
        }

        return $summary;
    }

    // --- Process trigger with strategies (merged from advanced version) ---
    protected function processTrigger($rule, $v, string $metric, string $code, int $count, string $winStart, string $winEnd, array &$summary): void
    {
        $summary['triggers']++;

        $strategy     = $rule->cool_off_strategy ?? 'create_new';
        $cooloffDays  = (int)($rule->cool_off_days ?? 14);
        $autoReopen   = (bool)($rule->auto_reopen ?? true);

        $chain = DB::table('tpr_rule_chains')
            ->where([
                'rule_id'=>$rule->id,
                'vendor_id'=>$v->id,
                'metric'=>$metric,
                'matched_code'=>$code,
            ])
            ->first();

        // Strategy: log_only
        if ($strategy === 'log_only') {
            $this->audit($rule, $v, $metric, $code, $count, 'cooloff_log', null, $chain->id ?? null, $winStart, $winEnd);
            $summary['cooloff_logs']++;
            return;
        }

        // Strategy: escalate_once
        if ($strategy === 'escalate_once') {
            if ($chain && $chain->issue_id) {
                if ($autoReopen && $this->isIssueClosed($chain->issue_id)) {
                    $this->reopenIssue($chain->issue_id);
                    DB::table('tpr_rule_chains')->where('id',$chain->id)->update(['status'=>'open','opened_at'=>now(),'closed_at'=>null,'updated_at'=>now()]);
                    $this->audit($rule, $v, $metric, $code, $count, 'reopen_issue', $chain->issue_id, $chain->id, $winStart, $winEnd);
                    $summary['reopened']++;
                    return;
                }
                $this->audit($rule, $v, $metric, $code, $count, 'once_skip', $chain->issue_id, $chain->id, $winStart, $winEnd);
                $summary['once_skips']++;
                return;
            }
        }

        // --- Default (create_new, reopen_existing, etc.) ---
        $recent = DB::table('tpr_rule_audit')
            ->where('rule_id',$rule->id)
            ->where('vendor_id',$v->id)
            ->where('matched_code',$code)
            ->where('action_taken','created_issue')
            ->where('triggered_at','>=', now()->subDays($cooloffDays)->toDateTimeString())
            ->exists();

        if ($recent) {
            $this->audit($rule, $v, $metric, $code, $count, 'cooloff_skip', $chain->issue_id ?? null, $chain->id ?? null, $winStart, $winEnd);
            $summary['cooloff_skips']++;
            return;
        }

        $issueId = $this->maybeCreateIssue($rule, $v, $metric, $code, $count);
        $chainId = $chain->id ?? DB::table('tpr_rule_chains')->insertGetId([
            'rule_id'=>$rule->id,
            'vendor_id'=>$v->id,
            'vendor_code'=>$v->code,
            'metric'=>$metric,
            'matched_code'=>$code,
            'issue_id'=>$issueId,
            'status'=>'open',
            'opened_at'=>now(),
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);

        if ($chain && !$chain->issue_id && $issueId) {
            DB::table('tpr_rule_chains')->where('id',$chainId)->update(['issue_id'=>$issueId,'status'=>'open','opened_at'=>now(),'updated_at'=>now()]);
        }

        $this->audit($rule, $v, $metric, $code, $count, $issueId ? 'created_issue' : 'rim_event', $issueId, $chainId, $winStart, $winEnd);
        if ($issueId) $summary['issues']++;
    }

    // --- Create issue if allowed ---
    protected function maybeCreateIssue($rule, $v, string $metric, string $code, int $count): ?int
    {
        if ($rule->action === 'rim_event_only' || !Schema::hasTable('issues')) return null;

        $title = $this->renderTpl($rule->title_template ?? '[TPR] {{metric}} threshold for {{vendor_code}}', [
            'metric'=>$metric,'vendor_code'=>$v->code,'vendor_name'=>$v->name,'matched_code'=>$code,'count'=>$count,
            'window_days'=>$rule->window_days,'rule_id'=>$rule->id
        ]);

        $desc = $this->renderTpl($rule->description_template ?? 'Rule {{rule_id}} triggered for {{vendor_code}}: {{count}} in {{window_days}} days ({{metric}}={{matched_code}}).', [
            'metric'=>$metric,'vendor_code'=>$v->code,'vendor_name'=>$v->name,'matched_code'=>$code,'count'=>$count,
            'window_days'=>$rule->window_days,'rule_id'=>$rule->id
        ]);

        return DB::table('issues')->insertGetId([
            'title'=> $title,
            'description'=> $desc,
            'priority'=> $rule->issue_priority ?? 'high',
            'status'=>'open',
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
    }

    protected function isIssueClosed(int $issueId): bool
    {
        if (!Schema::hasTable('issues')) return false;
        $row = DB::table('issues')->select('status')->where('id',$issueId)->first();
        return $row ? ($row->status !== 'open') : false;
    }

    protected function reopenIssue(int $issueId): void
    {
        if (!Schema::hasTable('issues')) return;
        DB::table('issues')->where('id',$issueId)->update(['status'=>'open','updated_at'=>now()]);
    }

    protected function autoCloseIfCleared($rule, $v, string $metric, string $code, string $since, array &$summary): void
    {
        $acDays = (int)($rule->auto_close_days ?? 0);
        if ($acDays <= 0 || !Schema::hasTable('issues')) return;

        $checkSince = now()->subDays($acDays)->toDateString();

        $chain = DB::table('tpr_rule_chains')->where([
            'rule_id'=>$rule->id,'vendor_id'=>$v->id,'metric'=>$metric,'matched_code'=>$code
        ])->first();
        if (!$chain || !$chain->issue_id) return;

        $close = false;
        if ($metric === 'kri') {
            $cnt = DB::table('tpr_vendor_kri_measures')
                ->where('vendor_id',$v->id)
                ->where('measured_at','>=',$checkSince)
                ->whereIn('status',['alert','breach'])
                ->where('kri_code',$code)->count();
            $close = $cnt < (int)$rule->threshold;
        } elseif ($metric === 'sla') {
            $cnt = DB::table('tpr_vendor_sla_measures')
                ->where('vendor_id',$v->id)
                ->where('measured_at','>=',$checkSince)
                ->where('status','breach')
                ->where('sla_code',$code)->count();
            $close = $cnt < (int)$rule->threshold;
        } elseif ($metric === 'composite') {
            $quiet = !$this->evalComposite($v->id, $rule, $checkSince);
            $close = $quiet;
        }

        if ($close) {
            DB::table('issues')->where('id',$chain->issue_id)->update(['status'=>'closed','updated_at'=>now()]);
            DB::table('tpr_rule_chains')->where('id',$chain->id)->update(['status'=>'closed','closed_at'=>now(),'updated_at'=>now()]);
            $this->audit($rule, $v, $metric, $code, 0, 'auto_close', $chain->issue_id, $chain->id, $checkSince, now()->toDateString());
            $summary['autoclosed']++;
        }
    }

    protected function audit($rule, $v, ?string $metric, ?string $code, int $count, string $action, ?int $issueId, ?int $chainId, ?string $wStart=null, ?string $wEnd=null): void
    {
        DB::table('tpr_rule_audit')->insert([
            'rule_id'=>$rule->id,
            'vendor_id'=>$v->id,'vendor_code'=>$v->code,
            'metric'=>$metric,'matched_code'=>$code,'count'=>$count,
            'window_start'=>$wStart ?? now()->subDays((int)$rule->window_days)->toDateString(),
            'window_end'=>$wEnd ?? now()->toDateString(),
            'action_taken'=>$action,'issue_id'=>$issueId,'chain_id'=>$chainId,
            'payload'=> json_encode(['rule'=>$rule]),
            'triggered_at'=> now(),'created_at'=> now(),'updated_at'=> now()
        ]);
    }

    protected function renderTpl(string $tpl, array $ctx): string
    {
        foreach ($ctx as $k=>$v) {
            $tpl = preg_replace('/{{\s*'.preg_quote($k,'/').'\s*}}/u', (string)$v, $tpl);
        }
        return $tpl;
    }

    protected function evalComposite(int $vendorId, $rule, string $since): bool
    {
        $expr = $rule->expression ?? '';
        if ($expr === '') return false;
        $threshold = (int)($rule->threshold ?? 1);

        $expr = preg_replace_callback("/kri\(\s*'([^']+)'\s*(?:,\s*(\d+))?\s*\)/i", function($m) use ($vendorId,$since,$threshold) {
            $pat = $m[1]; $th = isset($m[2]) ? (int)$m[2] : $threshold;
            $q = DB::table('tpr_vendor_kri_measures')->where('vendor_id',$vendorId)
                ->where('measured_at','>=',$since)->whereIn('status',['alert','breach'])
                ->where('kri_code','REGEXP',$pat)->count();
            return $q >= $th ? 'true' : 'false';
        }, $expr);

        $expr = preg_replace_callback("/sla\(\s*'([^']+)'\s*(?:,\s*(\d+))?\s*\)/i", function($m) use ($vendorId,$since,$threshold) {
            $pat = $m[1]; $th = isset($m[2]) ? (int)$m[2] : $threshold;
            $q = DB::table('tpr_vendor_sla_measures')->where('vendor_id',$vendorId)
                ->where('measured_at','>=',$since)->where('status','breach')
                ->where('sla_code','REGEXP',$pat)->count();
            return $q >= $th ? 'true' : 'false';
        }, $expr);

        $expr = preg_replace('/\bAND\b/i', '&&', $expr);
        $expr = preg_replace('/\bOR\b/i', '||', $expr);
        $expr = preg_replace('/\bNOT\b/i', '!', $expr);

        if (preg_match('/[^\s\(\)\!\&\|truefals]/i', str_replace(['true','false'],'', strtolower($expr)))) {
            return false;
        }
        try { return eval('return ('.$expr.');') ? true : false; } catch (\Throwable $e) { return false; }
    }
}
