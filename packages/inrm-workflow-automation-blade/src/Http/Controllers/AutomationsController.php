<?php
namespace Inrm\Workflow\Http\Controllers;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inrm\Workflow\Support\Engine;

class AutomationsController extends Controller
{
    public function index(){
        $rows=DB::table('workflow_automations')->orderBy('id')->paginate(25);
        return view('workflow::index', compact('rows'));
    }
    public function create(){ return view('workflow::create'); }
    public function store(Request $r){
        $data=$r->validate([
            'name'=>'required','enabled'=>'nullable','trigger_type'=>'required','interval_minutes'=>'nullable|integer|min:1',
            'expression'=>'nullable','filter'=>'nullable','actions'=>'nullable'
        ]);
        $data['enabled'] = $r->boolean('enabled', true);
        $data['filter'] = $data['filter'] ?? null;
        $data['created_at']=now(); $data['updated_at']=now();
        $id = DB::table('workflow_automations')->insertGetId([
            'name'=>$data['name'],'enabled'=>$data['enabled'],'trigger_type'=>$data['trigger_type'],
            'interval_minutes'=>$data['interval_minutes'] ?? 60,'expression'=>$data['expression'] ?? null,
            'filter'=>$data['filter'],'last_run_at'=>null,'event_since_at'=>now()->subDay(),'created_at'=>$data['created_at'],'updated_at'=>$data['updated_at']
        ]);
        $this->storeActions($id, $r->input('actions'));
        return redirect('/workflow');
    }
    public function show($id){
        $a = DB::table('workflow_automations')->where('id',$id)->first(); abort_unless($a,404);
        $actions = DB::table('workflow_actions')->where('automation_id',$id)->orderBy('order')->get();
        $runs = DB::table('workflow_runs')->where('automation_id',$id)->orderByDesc('started_at')->limit(50)->get();
        return view('workflow::show', compact('a','actions','runs'));
    }
    public function enable($id){ DB::table('workflow_automations')->where('id',$id)->update(['enabled'=>1,'updated_at'=>now()]); return back(); }
    public function disable($id){ DB::table('workflow_automations')->where('id',$id)->update(['enabled'=>0,'updated_at'=>now()]); return back(); }
    public function run($id){ $res=(new Engine())->runAutomation((int)$id); return redirect('/workflow/'.$id)->with('status', json_encode($res)); }

    protected function storeActions(int $autoId, $actionsJson = null): void
    {
        if (!$actionsJson) return;
        try { $list = json_decode($actionsJson, true); } catch (\Throwable $e) { $list = []; }
        $ord = 0;
        foreach ($list as $row) {
            DB::table('workflow_actions')->insert([
                'automation_id'=>$autoId,'order'=>$ord++,'type'=>$row['type'] ?? 'emit_rim','config'=>json_encode($row.get('config',[])),
                'created_at'=>now(),'updated_at'=>now()
            ]);
        }
    }
}
