<?php

namespace Inrm\TPR\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RulesRunController extends Controller
{
    public function run(Request $r)
    {
        $vendorId = $r->input('vendor_id');
        $now = now();
        $rules = DB::table('tpr_rules')->where('enabled', 1)->orderBy('id')->get()->all();

        $summary = [
            'evaluated'  => count($rules),
            'triggers'   => 0,
            'issues'     => 0,
            'audits'     => 0,
            'skipped'    => 0,
            'suppressed' => 0,
            'autoclosed' => 0,
        ];

        // Load suppressions
        $suppressions = DB::table('tpr_rule_suppressions')
            ->where('until', '>=', $now->toDateString())
            ->get()
            ->all();

        foreach ($rules as $rule) {
            $scope = json_decode($rule->scope ?? 'null', true) ?: [];

            $vendorsQ = DB::table('tpr_vendors')->select('id', 'code', 'name', 'tier', 'category', 'status');
            if (!empty($scope['vendor_id'])) $vendorsQ->where('id', $scope['vendor_id']);
            if (!empty($scope['tier'])) $vendorsQ->where('tier', $scope['tier']);
            if (!empty($scope['category'])) $vendorsQ->where('category', $scope['category']);
            if ($vendorId) $vendorsQ->where('id', $vendorId);
            $vendors = $vendorsQ->get()->all();

            foreach ($vendors as $v) {
                // --- Suppression handling ---
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
                    DB::table('tpr_rule_audit')->insert([
                        'rule_id'     => $rule->id,
                        'vendor_id'   => $v->id,
                        'vendor_code' => $v->code,
                        'metric'      => null,
                        'matched_code'=> null,
                        'count'       => 0,
                        'window_start'=> $now->clone()->subDays((int)$rule->window_days)->toDateString(),
                        'window_end'  => $now->toDateString(),
                        'action_taken'=> 'suppressed',
                        'issue_id'    => null,
                        'payload'     => json_encode(['reason' => 'suppressed', 'rule' => $rule]),
                        'triggered_at'=> now(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $summary['suppressed']++;
                    continue;
                }

                $winStart = $now->clone()->subDays((int)$rule->window_days)->toDateString();
                $winEnd   = $now->toDateString();

                // --- Composite rules ---
                if (($rule->logic_type ?? 'SIMPLE') === 'COMPOSITE' && $rule->expression) {
                    $this->processComposite($rule, $v, $winStart, $winEnd, $summary, $now);
                    continue;
                }

                // --- Simple KRI/SLA rules ---
                $this->processSimple($rule, $v, $winStart, $winEnd, $summary, $now);
            }
        }

        return $summary;
    }

    protected function processSimple($rule, $vendor, $winStart, $winEnd, &$summary, $now)
    {
        $matched = [];

        if ($rule->type === 'KRI_ALERTS_IN_WINDOW' || $rule->metric === 'kri') {
            $q = DB::table('tpr_vendor_kri_measures')->where('vendor_id', $vendor->id)
                ->where('measured_at', '>=', $winStart)
                ->whereIn('status', ['alert', 'breach']);
            if ($rule->code_pattern) $q->where('kri_code', 'REGEXP', $rule->code_pattern);

            $rows = $q->select('kri_code', DB::raw('COUNT(*) as n'))->groupBy('kri_code')->get()->all();
            foreach ($rows as $row) {
                if ((int)$row->n >= (int)$rule->threshold) {
                    $matched[] = ['metric' => 'kri', 'code' => $row->kri_code, 'count' => (int)$row->n];
                }
            }
        }

        if ($rule->type === 'SLA_BREACHES_IN_WINDOW' || $rule->metric === 'sla') {
            $q = DB::table('tpr_vendor_sla_measures')->where('vendor_id', $vendor->id)
                ->where('measured_at', '>=', $winStart)
                ->where('status', 'breach');
            if ($rule->code_pattern) $q->where('sla_code', 'REGEXP', $rule->code_pattern);

            $rows = $q->select('sla_code', DB::raw('COUNT(*) as n'))->groupBy('sla_code')->get()->all();
            foreach ($rows as $row) {
                if ((int)$row->n >= (int)$rule->threshold) {
                    $matched[] = ['metric' => 'sla', 'code' => $row->sla_code, 'count' => (int)$row->n];
                }
            }
        }

        foreach ($matched as $m) {
            $summary['triggers']++;

            // Dedup logic (daily dedupe OR cool-off dedupe)
            $recent = DB::table('tpr_rule_audit')
                ->where('rule_id', $rule->id)
                ->where('vendor_id', $vendor->id)
                ->where('matched_code', $m['code'])
                ->where('triggered_at', '>=', now()->subDays((int)($rule->cool_off_days ?? 14))->toDateTimeString())
                ->exists();

            if ($recent) {
                $summary['skipped']++;
                continue;
            }

            $actionTaken = 'noop';
            $issueId     = null;

            if ($rule->action === 'create_issue' && Schema::hasTable('issues')) {
                $title = $this->renderTpl($rule->title_template ?? '[TPR] {{metric}} threshold for {{vendor_code}}', [
                    'metric'      => $m['metric'],
                    'vendor_code' => $vendor->code,
                    'vendor_name' => $vendor->name,
                    'matched_code'=> $m['code'],
                    'count'       => $m['count'],
                    'window_days' => $rule->window_days,
                    'rule_id'     => $rule->id,
                ]);

                $desc = $this->renderTpl($rule->description_template ?? 'Rule {{rule_id}} triggered for {{vendor_code}}: {{count}} in {{window_days}} days ({{metric}}={{matched_code}}).', [
                    'metric'      => $m['metric'],
                    'vendor_code' => $vendor->code,
                    'vendor_name' => $vendor->name,
                    'matched_code'=> $m['code'],
                    'count'       => $m['count'],
                    'window_days' => $rule->window_days,
                    'rule_id'     => $rule->id,
                ]);

                $issueId = DB::table('issues')->insertGetId([
                    'title'       => $title,
                    'description' => $desc,
                    'priority'    => $rule->issue_priority ?? 'high',
                    'status'      => 'open',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $summary['issues']++;
                $actionTaken = 'created_issue';
            } elseif ($rule->action === 'rim_event_only' && Schema::hasTable('rim_events')) {
                DB::table('rim_events')->insert([
                    'type'      => 'TPR_RULE_TRIGGER',
                    'severity'  => 'medium',
                    'risk_id'   => null,
                    'message'   => "TPR rule trigger vendor={$vendor->code} metric={$m['metric']} code={$m['code']} count={$m['count']}",
                    'occurred_at'=> now(),
                    'metrics'   => json_encode([
                        'rule_id'    => $rule->id,
                        'vendor_id'  => $vendor->id,
                        'metric'     => $m['metric'],
                        'code'       => $m['code'],
                        'count'      => $m['count'],
                    ]),
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
                $actionTaken = 'rim_event';
            }

            DB::table('tpr_rule_audit')->insert([
                'rule_id'     => $rule->id,
                'vendor_id'   => $vendor->id,
                'vendor_code' => $vendor->code,
                'metric'      => $m['metric'],
                'matched_code'=> $m['code'],
                'count'       => $m['count'],
                'window_start'=> $winStart,
                'window_end'  => $winEnd,
                'action_taken'=> $actionTaken,
                'issue_id'    => $issueId,
                'payload'     => json_encode(['rule' => $rule, 'match' => $m]),
                'triggered_at'=> now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $summary['audits']++;

            // Auto-close logic (simple)
            $this->autoCloseSimple($rule, $vendor, $m, $summary);
        }
    }

    protected function processComposite($rule, $vendor, $winStart, $winEnd, &$summary, $now)
    {
        if ($this->evalComposite($vendor->id, $rule, $winStart)) {
        }
    }

    protected function autoCloseSimple($rule, $vendor, $match, &$summary)
    {
        $acDays = (int)($rule->auto_close_days ?? 0);
        if ($acDays <= 0 || !Schema::hasTable('issues')) return;

        $since = now()->subDays($acDays)->toDateString();
        $cnt   = 0;

        if ($match['metric'] === 'kri') {
            $cnt = DB::table('tpr_vendor_kri_measures')->where('vendor_id', $vendor->id)
                ->where('measured_at', '>=', $since)->whereIn('status', ['alert', 'breach'])
                ->where('kri_code', $match['code'])->count();
        } elseif ($match['metric'] === 'sla') {
            $cnt = DB::table('tpr_vendor_sla_measures')->where('vendor_id', $vendor->id)
                ->where('measured_at', '>=', $since)->where('status', 'breach')
                ->where('sla_code', $match['code'])->count();
        }

        if ($cnt < (int)$rule->threshold) {
            $openAudits = DB::table('tpr_rule_audit as a')
                ->where('a.rule_id', $rule->id)
                ->where('a.vendor_id', $vendor->id)
                ->where('a.action_taken', 'created_issue')
                ->where('a.matched_code', $match['code'])
                ->whereNotNull('a.issue_id')
                ->get()->all();

            foreach ($openAudits as $a) {
                $open = DB::table('issues')->where('id', $a->issue_id)->where('status', 'open')->exists();
                if ($open) {
                    DB::table('issues')->where('id', $a->issue_id)->update(['status' => 'closed', 'updated_at' => now()]);
                    DB::table('tpr_rule_audit')->insert([
                        'rule_id'     => $rule->id,
                        'vendor_id'   => $vendor->id,
                        'vendor_code' => $vendor->code,
                        'metric'      => $a->metric,
                        'matched_code'=> $a->matched_code,
                        'count'       => $cnt,
                        'window_start'=> $since,
                        'window_end'  => now()->toDateString(),
                        'action_taken'=> 'auto_close',
                        'issue_id'    => $a->issue_id,
                        'payload'     => json_encode(['reason' => 'below threshold in cool-off window']),
                        'triggered_at'=> now(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $summary['autoclosed']++;
                }
            }
        }
    }

    protected function renderTpl(string $tpl, array $ctx): string
    {
        foreach ($ctx as $k => $v) {
            $tpl = preg_replace('/{{\s*' . preg_quote($k, '/') . '\s*}}/u', (string)$v, $tpl);
        }
        return $tpl;
    }

    protected function evalComposite(int $vendorId, $rule, string $since): bool
    {
        $expr = $rule->expression ?? '';
        if ($expr === '') return false;
        $threshold = (int)($rule->threshold ?? 1);

        // Replace KRI tokens
        $expr = preg_replace_callback("/kri\(\s*'([^']+)'\s*(?:,\s*(\d+))?\s*\)/i", function ($m) use ($vendorId, $since, $threshold) {
            $pat = $m[1];
            $th  = isset($m[2]) ? (int)$m[2] : $threshold;
            $q   = DB::table('tpr_vendor_kri_measures')->where('vendor_id', $vendorId)
                ->where('measured_at', '>=', $since)->whereIn('status', ['alert', 'breach'])
                ->where('kri_code', 'REGEXP', $pat)->count();
            return $q >= $th ? 'true' : 'false';
        }, $expr);

        // Replace SLA tokens
        $expr = preg_replace_callback("/sla\(\s*'([^']+)'\s*(?:,\s*(\d+))?\s*\)/i", function ($m) use ($vendorId, $since, $threshold) {
            $pat = $m[1];
            $th  = isset($m[2]) ? (int)$m[2] : $threshold;
            $q   = DB::table('tpr_vendor_sla_measures')->where('vendor_id', $vendorId)
                ->where('measured_at', '>=', $since)->where('status', 'breach')
                ->where('sla_code', 'REGEXP', $pat)->count();
            return $q >= $th ? 'true' : 'false';
        }, $expr);

        $expr = preg_replace('/\bAND\b/i', '&&', $expr);
        $expr = preg_replace('/\bOR\b/i', '||', $expr);
        $expr = preg_replace('/\bNOT\b/i', '!', $expr);

        if (preg_match('/[^\s\(\)\!\&\|truefals]/i', str_replace(['true', 'false'], '', strtolower($expr)))) {
            return false;
        }

        try {
            return eval('return (' . $expr . ');') ? true : false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
