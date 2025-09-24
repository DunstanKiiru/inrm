<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\RiskConsequence;
use Illuminate\Http\Request;

class RiskConsequenceController extends Controller {
  public function index(){ return RiskConsequence::orderBy('name')->get(); }
  public function store(Request $r){ $data=$r->validate(['name'=>'required|string|max:120']); return RiskConsequence::create($data); }
  public function update(Request $r, RiskConsequence $riskConsequence){ $riskConsequence->update($r->validate(['name'=>'required|string|max:120'])); return $riskConsequence->fresh(); }
  public function destroy(RiskConsequence $riskConsequence){ $riskConsequence->delete(); return response()->noContent(); }
}
