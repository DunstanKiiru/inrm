<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRiskCategoryRequest;
use App\Http\Requests\UpdateRiskCategoryRequest;
use App\Models\RiskCategory;
use Illuminate\Http\Request;

class RiskCategoryController extends Controller {
  public function index(){
    $roots = RiskCategory::with('children')->whereNull('parent_id')->orderBy('name')->get();
    return $roots;
  }
  public function store(StoreRiskCategoryRequest $r){ return RiskCategory::create($r->validated()); }
  public function update(UpdateRiskCategoryRequest $r, RiskCategory $riskCategory){ $riskCategory->update($r->validated()); return $riskCategory->fresh(); }
  public function destroy(RiskCategory $riskCategory){ $riskCategory->delete(); return response()->noContent(); }
}
