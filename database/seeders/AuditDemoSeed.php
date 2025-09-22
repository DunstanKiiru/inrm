<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\AuditPlan;
use App\Models\AuditProcedure;
use App\Models\AuditSample;
use App\Models\AuditFinding;
use App\Models\AuditFollowUp;

class AuditDemoSeed extends Seeder {
  public function run(): void {
    $plan = AuditPlan::firstOrCreate(['ref'=>'AP-2025-001'],[
      'title'=>'Payroll Process Audit',
      'scope'=>'Payroll end-to-end for FY 2024',
      'period_start'=>now()->subMonths(12)->startOfMonth(),
      'period_end'=>now()->subMonth()->endOfMonth(),
      'status'=>'fieldwork',
      'objectives'=>'Assess design and operating effectiveness of payroll controls',
      'methodology'=>'Walkthroughs, sampling (random), re-performance'
    ]);

    $proc = AuditProcedure::firstOrCreate(['audit_plan_id'=>$plan->id,'ref'=>'P01'],[
      'title'=>'Payroll completeness testing',
      'description'=>'Sample monthly payroll runs; verify inputs to outputs.',
      'status'=>'testing','population_size'=>12,'sample_method'=>'random','sample_size'=>5
    ]);

    for($i=1; $i<=5; $i++){
      AuditSample::firstOrCreate(['audit_procedure_id'=>$proc->id,'sample_no'=>$i],[
        'population_ref'=>"2024-".str_pad($i,2,'0',STR_PAD_LEFT),
        'attributes_json'=>['gross'=>rand(100000,200000)/100,'net'=>rand(70000,150000)/100],
        'tested_at'=>now()->subDays(10-$i),
        'result'=> $i===3 ? 'exception' : 'pass',
        'notes'=> $i===3 ? 'Variance over threshold; investigate.' : null
      ]);
    }

    $finding = AuditFinding::firstOrCreate(['audit_plan_id'=>$plan->id,'title'=>'User access review not performed'],[
      'audit_procedure_id'=>$proc->id,
      'description'=>'Quarterly access review evidence not available for Q2.',
      'severity'=>'high','rating'=>'effectiveness','cause'=>'Process oversight','impact'=>'Risk of unauthorized payroll changes',
      'criteria'=>'SOP PR-AC-02 requires quarterly reviews','condition'=>'No evidence for Q2','recommendation'=>'Implement calendarized reminders and approvals.',
      'status'=>'open','target_date'=>now()->addMonths(1)
    ]);

    AuditFollowUp::firstOrCreate(['finding_id'=>$finding->id,'result'=>null],[
      'test_date'=>now()->addDays(40),
      'notes'=>'Schedule follow-up after remediation complete.'
    ]);
  }
}
