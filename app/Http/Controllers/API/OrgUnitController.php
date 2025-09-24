<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\OrgUnit;
use Illuminate\Http\Request;

class OrgUnitController extends Controller {
  public function index(){
    return OrgUnit::with('children')->whereNull('parent_id')->orderBy('name')->get();
  }
  public function store(Request $r){ $data=$r->validate(['name'=>'required|string|max:120','parent_id'=>'nullable|exists:org_units,id']); return OrgUnit::create($data); }
  public function update(Request $r, OrgUnit $orgUnit){ $orgUnit->update($r->validate(['name'=>'required|string|max:120','parent_id'=>'nullable|exists:org_units,id'])); return $orgUnit->fresh(); }
  public function destroy(OrgUnit $orgUnit){ $orgUnit->delete(); return response()->noContent(); }
}
