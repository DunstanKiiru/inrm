<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssessmentTemplate;

class AssessmentTemplateController extends Controller {
  public function index(){ return AssessmentTemplate::orderBy('title')->get(); }
  public function store(Request $r){
    $data=$r->validate([
      'title'=>'required','description'=>'nullable','entity_type'=>'required|in:risk,org_unit',
      'schema_json'=>'required','ui_schema_json'=>'nullable','status'=>'nullable'
    ]);
    return AssessmentTemplate::create($data);
  }
  public function update(Request $r, AssessmentTemplate $template){ $template->update($r->all()); return $template->fresh(); }
  public function destroy(AssessmentTemplate $template){ $template->delete(); return response()->noContent(); }
  public function show(AssessmentTemplate $template){ return $template; }
}
