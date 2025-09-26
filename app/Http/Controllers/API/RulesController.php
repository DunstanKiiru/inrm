<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RulesController extends Controller
{
    public function index(Request $r)
    {
        $q = DB::table('tpr_rules');

        if ($r->filled('enabled')) {
            $q->where('enabled', (bool) $r->input('enabled'));
        }
        if ($r->filled('type')) {
            $q->where('type', $r->input('type'));
        }

        return $q->orderByDesc('created_at')->paginate(100);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'type'                 => 'required|string',
            'metric'               => 'nullable|string',
            'code_pattern'         => 'nullable|string',
            'window_days'          => 'nullable|integer',
            'threshold'            => 'nullable|integer',
            'cool_off_days'        => 'nullable|integer',
            'auto_close_days'      => 'nullable|integer',
            'cool_off_strategy'    => 'nullable|string',
            'auto_reopen'          => 'nullable|boolean',
            'scope'                => 'array',
            'enabled'              => 'nullable|boolean',
            'action'               => 'nullable|string',
            'issue_priority'       => 'nullable|string',
            'title_template'       => 'nullable|string',
            'description_template' => 'nullable|string',
            'logic_type'           => 'nullable|string',
            'expression'           => 'nullable|string',
        ]);

        $id = DB::table('tpr_rules')->insertGetId([
            'type'                 => $data['type'],
            'metric'               => $data['metric'] ?? null,
            'code_pattern'         => $data['code_pattern'] ?? null,
            'window_days'          => $data['window_days'] ?? 30,
            'threshold'            => $data['threshold'] ?? 3,
            'cool_off_days'        => $data['cool_off_days'] ?? 14,
            'auto_close_days'      => $data['auto_close_days'] ?? 7,
            'cool_off_strategy'    => $data['cool_off_strategy'] ?? 'create_new',
            'auto_reopen'          => $data['auto_reopen'] ?? true,
            'scope'                => json_encode($data['scope'] ?? null),
            'enabled'              => $data['enabled'] ?? true,
            'action'               => $data['action'] ?? 'create_issue',
            'issue_priority'       => $data['issue_priority'] ?? 'high',
            'title_template'       => $data['title_template'] ?? '[TPR] {{metric}} threshold for {{vendor_code}}',
            'description_template' => $data['description_template']
                ?? 'Rule {{rule_id}} triggered for {{vendor_code}}: {{count}} in {{window_days}} days ({{metric}}={{matched_code}}).',
            'logic_type'           => $data['logic_type'] ?? 'SIMPLE',
            'expression'           => $data['expression'] ?? null,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        return DB::table('tpr_rules')->where('id', $id)->first();
    }

    public function update($id, Request $r)
    {
        $data = $r->validate([
            'type'                 => 'nullable|string',
            'metric'               => 'nullable|string',
            'code_pattern'         => 'nullable|string',
            'window_days'          => 'nullable|integer',
            'threshold'            => 'nullable|integer',
            'cool_off_days'        => 'nullable|integer',
            'auto_close_days'      => 'nullable|integer',
            'cool_off_strategy'    => 'nullable|string',
            'auto_reopen'          => 'nullable|boolean',
            'scope'                => 'array',
            'enabled'              => 'nullable|boolean',
            'action'               => 'nullable|string',
            'issue_priority'       => 'nullable|string',
            'title_template'       => 'nullable|string',
            'description_template' => 'nullable|string',
            'logic_type'           => 'nullable|string',
            'expression'           => 'nullable|string',
        ]);

        DB::table('tpr_rules')->where('id', $id)->update(array_merge($data, [
            'scope'      => array_key_exists('scope', $data)
                ? json_encode($data['scope'])
                : DB::raw('scope'),
            'updated_at' => now(),
        ]));

        return DB::table('tpr_rules')->where('id', $id)->first();
    }

    public function destroy($id)
    {
        DB::table('tpr_rules')->where('id', $id)->delete();
        return ['ok' => true];
    }

    public function audit(Request $r)
    {
        $q = DB::table('tpr_rule_audit as a')
            ->leftJoin('tpr_rules as r', 'r.id', '=', 'a.rule_id')
            ->select(
                'a.*',
                'r.type',
                'r.metric as rule_metric',
                'r.code_pattern',
                'r.window_days',
                'r.threshold',
                'r.logic_type',
                'r.cool_off_strategy',
                'r.auto_reopen'
            );

        if ($r->filled('rule_id')) {
            $q->where('a.rule_id', $r->input('rule_id'));
        }
        if ($r->filled('vendor_id')) {
            $q->where('a.vendor_id', $r->input('vendor_id'));
        }
        if ($r->filled('days')) {
            $q->where(
                'a.triggered_at',
                '>=',
                now()->subDays((int)$r->input('days'))->toDateTimeString()
            );
        }

        return $q->orderByDesc('a.triggered_at')->paginate(200);
    }
}
