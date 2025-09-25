<?php

namespace Inrm\Workflow\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1) Nightly board-pack snapshot
        $auto1 = DB::table('workflow_automations')->insertGetId([
            'name'=>'Nightly Board Pack Snapshot','enabled'=>1,'trigger_type'=>'SCHEDULE','interval_minutes'=>1440,
            'expression'=>null,'filter'=>null,'last_run_at'=>null,'event_since_at'=>$now->copy()->subDay(),
            'created_at'=>$now,'updated_at'=>$now
        ]);
        DB::table('workflow_actions')->insert([
            ['automation_id'=>$auto1,'order'=>0,'type'=>'snapshot_boardpack','config'=>json_encode([]),'created_at'=>$now,'updated_at'=>$now],
            ['automation_id'=>$auto1,'order'=>1,'type'=>'webhook_post','config'=>json_encode(['url'=>'https://httpbin.org/post','payload'=>['event'=>'boardpack_snapshot']]),'created_at'=>$now,'updated_at'=>$now],
        ]);

        // 2) RIM critical test fails => create incident
        $auto2 = DB::table('workflow_automations')->insertGetId([
            'name'=>'RIM Crit Fail → Incident','enabled'=>1,'trigger_type'=>'RIM','interval_minutes'=>60,
            'expression'=>'control\.test_failed|control\.delta_adverse','filter'=>json_encode(['level'=>'crit']),
            'last_run_at'=>null,'event_since_at'=>$now->copy()->subHours(12),
            'created_at'=>$now,'updated_at'=>$now
        ]);
        DB::table('workflow_actions')->insert([
            ['automation_id'=>$auto2,'order'=>0,'type'=>'create_incident','config'=>json_encode(['title'=>'Control failure detected','severity'=>'high','description'=>'Auto-created from RIM event']), 'created_at'=>$now,'updated_at'=>$now],
            ['automation_id'=>$auto2,'order'=>1,'type'=>'emit_rim','config'=>json_encode(['type'=>'workflow.incident_opened','payload'=>['source'=>'rim']]), 'created_at'=>$now,'updated_at'=>$now],
        ]);

        // 3) TPR retriggers/escalations → emit rim + run_tpr
        $auto3 = DB::table('workflow_automations')->insertGetId([
            'name'=>'TPR Retrigger/Escalation Digest','enabled'=>1,'trigger_type'=>'TPR','interval_minutes'=>30,
            'expression'=>'retrigger|escalated','filter'=>json_encode(['tier'=>'critical']),
            'last_run_at'=>null,'event_since_at'=>$now->copy()->subHours(12),
            'created_at'=>$now,'updated_at'=>$now
        ]);
        DB::table('workflow_actions')->insert([
            ['automation_id'=>$auto3,'order'=>0,'type'=>'emit_rim','config'=>json_encode(['type'=>'tpr.retrigger.digest']), 'created_at'=>$now,'updated_at'=>$now],
            ['automation_id'=>$auto3,'order'=>1,'type'=>'run_tpr','config'=>json_encode([]), 'created_at'=>$now,'updated_at'=>$now],
        ]);
    }
}
