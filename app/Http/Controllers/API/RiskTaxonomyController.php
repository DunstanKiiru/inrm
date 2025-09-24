<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Risk;
use App\Models\RiskCause;
use App\Models\RiskConsequence;

class RiskTaxonomyController extends Controller {
  public function get(Risk $risk){
    return [
      'cause_ids' => $risk->causes()->pluck('risk_cause_id'),
      'consequence_ids' => $risk->consequences()->pluck('risk_consequence_id'),
    ];
  }
  public function set(Request $r, Risk $risk){
    $data = $r->validate([
      'cause_ids'=>'array','cause_ids.*'=>'exists:risk_causes,id',
      'consequence_ids'=>'array','consequence_ids.*'=>'exists:risk_consequences,id',
    ]);
    if(isset($data['cause_ids'])){ $risk->causes()->sync($data['cause_ids']); }
    if(isset($data['consequence_ids'])){ $risk->consequences()->sync($data['consequence_ids']); }
    return $this->get($risk);
  }
}
