<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\RiskCause;
use Illuminate\Http\Request;

class RiskCauseController extends Controller {
  public function index(){ return RiskCause::orderBy('name')->get(); }
  public function store(Request $r){ $data=$r->validate(['name'=>'required|string|max:120']); return RiskCause::create($data); }
  public function update(Request $r, RiskCause $riskCause){ $riskCause->update($r->validate(['name'=>'required|string|max:120'])); return $riskCause->fresh(); }
  public function destroy(RiskCause $riskCause){ $riskCause->delete(); return response()->noContent(); }
}
