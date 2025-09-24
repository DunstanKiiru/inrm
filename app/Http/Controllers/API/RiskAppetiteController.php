<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Risk;
use App\Models\RiskAppetiteProfile;
use App\Models\RiskThreshold;

class RiskAppetiteController extends Controller {
  public function profiles(){ return RiskAppetiteProfile::withCount('thresholds')->orderBy('name')->get(); }
  public function storeProfile(Request $r){ $data=$r->validate(['name'=>'required','description'=>'nullable']); return RiskAppetiteProfile::create($data); }
  public function thresholds(RiskAppetiteProfile $profile){ return $profile->thresholds()->orderBy('id')->get(); }
  public function storeThreshold(Request $r, RiskAppetiteProfile $profile){
    $data=$r->validate([
      'category_id'=>'nullable|exists:risk_categories,id',
      'owner_role'=>'nullable|string|max:100',
      'metric'=>'required|in:inherent,residual',
      'operator'=>'required|in:<=,<,=,>=,>',
      'limit'=>'required|numeric',
      'band'=>'required|in:Low,Medium,High,Extreme',
      'color'=>'nullable|string|max:20'
    ]);
    $data['profile_id']=$profile->id;
    return RiskThreshold::create($data);
  }
  public function breaches(Request $r, Risk $risk){
    $profileId = (int)($r->query('profile_id') ?: 0);
    $profile = $profileId ? RiskAppetiteProfile::find($profileId) : RiskAppetiteProfile::first();
    if(!$profile){ return []; }
    $ths = RiskThreshold::where('profile_id',$profile->id)
      ->where(function($q) use ($risk){ $q->whereNull('category_id')->orWhere('category_id',$risk->category_id); })
      ->get();
    $valInh = $risk->inherent_score;
    $valRes = $risk->residual_score ?? $valInh;
    $hits = [];
    foreach($ths as $t){
      $val = $t->metric === 'residual' ? $valRes : $valInh;
      $ok = match($t->operator){
        '<=' => $val <= $t->limit,
        '<'  => $val <  $t->limit,
        '='  => $val == $t->limit,
        '>=' => $val >= $t->limit,
        '>'  => $val >  $t->limit,
      };
      if($ok){ $hits[] = $t; }
    }
    return $hits;
  }
}
