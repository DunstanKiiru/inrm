<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AuditPlan;
use App\Models\AuditProcedure;
use App\Models\AuditSample;
use App\Models\AuditFinding;
use App\Models\AuditFollowUp;

class AuditPlanController extends Controller {

  // Plans
  public function index(Request $r){
    $q = AuditPlan::query();
    if($r->filled('status')) $q->where('status',$r->status);
    if($r->filled('search')) $q->where(function($w) use ($r){ $w->where('title','like','%'.$r->search.'%')->orWhere('ref','like','%'.$r->search.'%'); });
    return $q->orderByDesc('id')->paginate(20);
  }
  public function show(AuditPlan $plan){
    return $plan->load('lead','procedures.samples','findings.followups');
  }
  public function store(Request $r){
    $data=$r->validate(['title'=>'required','scope'=>'nullable','period_start'=>'nullable|date','period_end'=>'nullable|date','lead_id'=>'nullable|integer']);
    $ref = 'AP-'.date('Y').'-'.strtoupper(bin2hex(random_bytes(2)));
    $plan = AuditPlan::create(array_merge($data,['ref'=>$ref,'status'=>'planned']));
    return $plan;
  }
  public function update(Request $r, AuditPlan $plan){
    $plan->update($r->all()); return $plan->fresh();
  }

  // Procedures
  public function addProcedure(Request $r, AuditPlan $plan){
    $data=$r->validate(['title'=>'required','description'=>'nullable','tester_id'=>'nullable|integer','population_size'=>'nullable|integer','sample_method'=>'nullable','sample_size'=>'nullable|integer']);
    $ref = 'P'.str_pad(($plan->procedures()->count()+1),2,'0',STR_PAD_LEFT);
    $p = $plan->procedures()->create(array_merge($data,['ref'=>$ref]));
    return $p;
  }
  public function updateProcedure(Request $r, AuditPlan $plan, AuditProcedure $procedure){
    if($procedure->audit_plan_id !== $plan->id) return response()->json(['error'=>'Mismatch'], 422);
    $procedure->update($r->all()); return $procedure->fresh();
  }

  // Samples
  public function addSample(Request $r, AuditPlan $plan, AuditProcedure $procedure){
    if($procedure->audit_plan_id !== $plan->id) return response()->json(['error'=>'Mismatch'], 422);
    $data=$r->validate(['population_ref'=>'nullable','attributes_json'=>'array','tested_at'=>'nullable|date','result'=>'nullable','notes'=>'nullable']);
    $n = ($procedure->samples()->max('sample_no') ?? 0) + 1;
    $s = $procedure->samples()->create(array_merge($data,['sample_no'=>$n]));
    return $s;
  }
  public function bulkSamples(Request $r, AuditPlan $plan, AuditProcedure $procedure){
    if($procedure->audit_plan_id !== $plan->id) return response()->json(['error'=>'Mismatch'], 422);
    $rows=$r->validate(['rows'=>'required|array'])['rows'];
    $n = ($procedure->samples()->max('sample_no') ?? 0);
    $out=[];
    foreach($rows as $row){
      $n++;
      $out[] = $procedure->samples()->create([
        'sample_no'=>$n,
        'population_ref'=>$row['population_ref'] ?? null,
        'attributes_json'=>$row['attributes_json'] ?? null,
        'tested_at'=>$row['tested_at'] ?? null,
        'result'=>$row['result'] ?? null,
        'notes'=>$row['notes'] ?? null,
      ]);
    }
    return $out;
  }

  // Findings
  public function addFinding(Request $r, AuditPlan $plan){
    $data=$r->validate([
      'audit_procedure_id'=>'nullable|integer','title'=>'required','description'=>'nullable',
      'severity'=>'required|in:low,medium,high,critical','rating'=>'nullable|in:design,effectiveness',
      'cause'=>'nullable','impact'=>'nullable','criteria'=>'nullable','condition'=>'nullable',
      'recommendation'=>'nullable','owner_id'=>'nullable|integer','target_date'=>'nullable|date','risk_id'=>'nullable|integer'
    ]);
    $data['audit_plan_id'] = $plan->id;
    $f = AuditFinding::create($data);
    return $f;
  }
  public function updateFinding(Request $r, AuditPlan $plan, AuditFinding $finding){
    if($finding->audit_plan_id !== $plan->id) return response()->json(['error'=>'Mismatch'], 422);
    $finding->update($r->all()); return $finding->fresh();
  }

  // Follow-ups
  public function addFollowUp(Request $r, AuditPlan $plan, AuditFinding $finding){
    if($finding->audit_plan_id !== $plan->id) return response()->json(['error'=>'Mismatch'], 422);
    $data=$r->validate(['test_date'=>'nullable|date','result'=>'nullable|in:pass,fail','notes'=>'nullable','tester_id'=>'nullable|integer','evidence_url'=>'nullable']);
    $fu = $finding->followups()->create($data);
    return $fu;
  }

}
