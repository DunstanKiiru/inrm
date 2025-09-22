<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\PolicyVersion;
use App\Models\PolicyAttestation;

class AttestationController extends Controller {
  // List policies user must attest (published & require_attestation) with completion status
  public function myList(Request $r){
    $user = $r->user();
    $policies = Policy::where('status','publish')->where('require_attestation',true)->with('latestVersion')->orderBy('title')->get();
    $rows = [];
    foreach($policies as $p){
      $ver = $p->latestVersion;
      $done = PolicyAttestation::where('policy_id',$p->id)->where('policy_version_id',$ver->id)->where('user_id',$user->id)->exists();
      $rows[] = ['policy'=>$p, 'version'=>$ver, 'attested'=>$done];
    }
    return $rows;
  }

  public function attest(Request $r, Policy $policy){
    $user = $r->user();
    $ver = $policy->latestVersion()->first();
    if(!$ver) return response()->json(['error'=>'No version'], 422);
    $exists = PolicyAttestation::where(['policy_id'=>$policy->id,'policy_version_id'=>$ver->id,'user_id'=>$user->id])->first();
    if($exists) return $exists;
    return PolicyAttestation::create(['policy_id'=>$policy->id,'policy_version_id'=>$ver->id,'user_id'=>$user->id,'attested_at'=>now()]);
  }
}
