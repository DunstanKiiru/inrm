<?php
namespace App\Http\Controllers\Api;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChainsController extends Controller
{
    public function index(Request $r)
    {
        $q = DB::table('tpr_rule_chains as c')
            ->leftJoin('tpr_rules as r','r.id','=','c.rule_id')
            ->select('c.*','r.type','r.metric as rule_metric','r.window_days','r.threshold','r.cool_off_strategy','r.auto_close_days','r.auto_reopen');
        if ($r->filled('rule_id')) $q->where('c.rule_id',$r->input('rule_id'));
        if ($r->filled('vendor_id')) $q->where('c.vendor_id',$r->input('vendor_id'));
        if ($r->filled('metric')) $q->where('c.metric',$r->input('metric'));
        $rows = $q->orderByDesc('c.updated_at')->paginate(100);

        $includeAudits = filter_var($r->input('include_audits', false), FILTER_VALIDATE_BOOL);
        $last = (int)($r->input('last', 5));

        $items = $rows->items();
        $ids = array_map(fn($i)=>$i->id, $items);
        $auditsByChain = [];
        if ($includeAudits && count($ids)) {
            $audits = DB::table('tpr_rule_audit')->whereIn('chain_id',$ids)->orderByDesc('triggered_at')->get()->all();
            foreach ($audits as $a) {
                $auditsByChain[$a->chain_id] = $auditsByChain[$a->chain_id] ?? [];
                if (count($auditsByChain[$a->chain_id]) < $last) $auditsByChain[$a->chain_id][] = $a;
            }
        }

        $rows->getCollection()->transform(function($c) use ($auditsByChain) {
            $opened = $c->opened_at ? strtotime($c->opened_at) : null;
            $ended  = $c->status === 'open' ? time() : ($c->closed_at ? strtotime($c->closed_at) : time());
            $c->dwell_seconds = $opened ? max(0, $ended - $opened) : null;
            if (isset($auditsByChain[$c->id])) $c->last_audits = $auditsByChain[$c->id];
            return $c;
        });

        return $rows;
    }

    public function summary($id, Request $r)
    {
        $c = DB::table('tpr_rule_chains as c')
            ->leftJoin('tpr_rules as r','r.id','=','c.rule_id')
            ->select('c.*','r.type','r.metric as rule_metric','r.window_days','r.threshold','r.cool_off_strategy','r.auto_close_days','r.auto_reopen')
            ->where('c.id',$id)->first();
        if (!$c) return response()->json(['error'=>'Not found'], 404);
        $last = (int)$r->input('last', 10);
        $audits = DB::table('tpr_rule_audit')->where('chain_id',$c->id)->orderByDesc('triggered_at')->limit($last)->get();
        $opened = $c->opened_at ? strtotime($c->opened_at) : null;
        $ended  = $c->status === 'open' ? time() : ($c->closed_at ? strtotime($c->closed_at) : time());
        $dwell = $opened ? max(0, $ended - $opened) : null;
        return ['chain'=>$c,'dwell_seconds'=>$dwell,'last_audits'=>$audits];
    }
}
