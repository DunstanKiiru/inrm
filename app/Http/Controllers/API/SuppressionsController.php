<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuppressionsController extends Controller
{
    public function index(Request $r)
    {
        $q = DB::table('tpr_rule_suppressions');
        if ($r->filled('rule_id')) $q->where('rule_id',$r->input('rule_id'));
        if ($r->filled('vendor_id')) $q->where('vendor_id',$r->input('vendor_id'));
        return $q->orderByDesc('until')->paginate(200);
    }
    public function store(Request $r)
    {
        $data = $r->validate([
            'rule_id'=>'nullable|integer','vendor_id'=>'nullable|integer',
            'until'=>'required|date','reason'=>'nullable|string'
        ]);
        $id = DB::table('tpr_rule_suppressions')->insertGetId([
            'rule_id'=>$data['rule_id'] ?? null,'vendor_id'=>$data['vendor_id'] ?? null,
            'until'=>$data['until'],'reason'=>$data['reason'] ?? null,
            'created_at'=>now(),'updated_at'=>now()
        ]);
        return DB::table('tpr_rule_suppressions')->where('id',$id)->first();
    }
    public function destroy($id)
    {
        DB::table('tpr_rule_suppressions')->where('id',$id)->delete();
        return ['ok'=>true];
    }
}
