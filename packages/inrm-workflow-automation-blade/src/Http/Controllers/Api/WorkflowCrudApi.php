<?php
namespace Inrm\Workflow\Http\Controllers\Api;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inrm\Workflow\Support\Engine;

class WorkflowCrudApi extends Controller
{
    public function list(Request $r){
        $q = DB::table('workflow_automations')->orderBy('id');
        if ($s = $r->query('search')) $q->where('name','like','%'.$s.'%');
        $rows = $q->get();
        return ['data'=>$rows];
    }
    public function detail($id){
        $a = DB::table('workflow_automations')->where('id',$id)->first();
        if (!$a) return response()->json(['error'=>'not_found'],404);
        $actions = DB::table('workflow_actions')->where('automation_id',$id)->orderBy('order')->get();
        $runs = DB::table('workflow_runs')->where('automation_id',$id)->orderByDesc('started_at')->limit(50)->get();
        $runIds = $runs->pluck('id')->all();
        $logs = empty($runIds) ? [] : DB::table('workflow_logs')->whereIn('run_id',$runIds)->orderBy('id')->get();
        return ['automation'=>$a,'actions'=>$actions,'runs'=>$runs,'logs'=>$logs];
    }
    public function create(Request $r){
        $data=$r->validate([
            'name'=>'required','enabled'=>'boolean','trigger_type'=>'required|in:SCHEDULE,RIM,TPR,INCIDENTS','interval_minutes'=>'integer|min:1',
            'expression'=>'nullable|string','filter'=>'nullable'
        ]);
        $now = now();
        $id = DB::table('workflow_automations')->insertGetId([
            'name'=>$data['name'],'enabled'=>$data['enabled']??true,'trigger_type'=>$data['trigger_type'],
            'interval_minutes'=>$data['interval_minutes'] ?? 60,'expression'=>$data['expression'] ?? null,
            'filter'=>$data['filter'] ?? null,'last_run_at'=>null,'event_since_at'=>$now->copy()->subDay(),
            'created_at'=>$now,'updated_at'=>$now
        ]);
        $actions = $r->input('actions', []);
        $ord=0;
        foreach ($actions as $a) {
            DB::table('workflow_actions')->insert([
                'automation_id'=>$id,'order'=>$ord++,'type'=>$a['type'] ?? 'emit_rim','config'=>json_encode($a['config'] ?? []),
                'created_at'=>$now,'updated_at'=>$now
            ]);
        }
        return ['id'=>$id];
    }
    public function toggle($id){
        $a = DB::table('workflow_automations')->where('id',$id)->first();
        if (!$a) return response()->json(['error'=>'not_found'],404);
        DB::table('workflow_automations')->where('id',$id)->update(['enabled'=>!$a->enabled,'updated_at'=>now()]);
        return ['enabled'=>!$a->enabled];
    }
    public function run($id){ return (new Engine())->runAutomation((int)$id); }
    public function runDue(){ return (new Engine())->runDue(); }
}
