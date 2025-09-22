<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Policy;
use App\Models\PolicyVersion;
use App\Models\PolicyAttestation;

class PolicyController extends Controller {
  public function index(Request $r){
    $q = Policy::with('latestVersion')->orderBy('title');
    if($r->filled('status')) $q->where('status',$r->status);
    return $q->paginate(20);
  }
  public function show(Policy $policy){
    return $policy->load('owner','versions','latestVersion');
  }
  public function store(Request $r){
    $data=$r->validate([ 'title'=>'required', 'require_attestation'=>'boolean' ]);
    $p = Policy::create(['title'=>$data['title'],'status'=>'draft','require_attestation'=>$data['require_attestation'] ?? true]);
    $v = PolicyVersion::create(['policy_id'=>$p->id,'version'=>1,'body_html'=>'<p>New policy</p>']);
    return $p->load('versions');
  }
  public function update(Request $r, Policy $policy){
    $policy->update($r->all());
    return $policy->fresh()->load('latestVersion');
  }
  public function addVersion(Request $r, Policy $policy){
    $data=$r->validate(['body_html'=>'required','notes'=>'nullable']);
    $latest = $policy->versions()->max('version') ?? 0;
    $v = $policy->versions()->create(['version'=>$latest+1,'body_html'=>$data['body_html'],'notes'=>$data['notes'] ?? null]);
    return $v;
  }
  public function transition(Request $r, Policy $policy){
    $to = $r->validate(['to_status'=>'required|string'])['to_status'];
    $valid = ['draft','review','approve','publish','retired'];
    if(!in_array($to, $valid)) return response()->json(['error'=>'Invalid status'], 422);
    $upd = ['status'=>$to];
    if($to==='publish'){ $upd['effective_date'] = now(); }
    $policy->update($upd);
    return $policy->fresh();
  }
}
