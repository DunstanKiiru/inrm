<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Models\AssessmentTemplate;
use App\Models\AssessmentRound;
use App\Models\AssessmentResponse;

class AssessmentController extends Controller {
  public function index(Request $r){
    $q = Assessment::with('template')->orderByDesc('id');
    if($r->filled('entity_type')) $q->where('entity_type',$r->entity_type);
    if($r->filled('entity_id')) $q->where('entity_id',$r->entity_id);
    if($r->filled('status')) $q->where('status',$r->status);
    return $q->paginate(20);
  }
  public function store(Request $r){
    $data=$r->validate([
      'template_id'=>'required|exists:assessment_templates,id',
      'entity_type'=>'required|in:risk,org_unit',
      'entity_id'=>'required|integer',
      'title'=>'required|string',
      'rounds'=>'nullable|integer|min:1',
      'first_due_at'=>'nullable|date'
    ]);
    $ass = Assessment::create($data);
    $rounds = max(1, (int)($data['rounds'] ?? 1));
    for($i=1;$i<=$rounds;$i++){
      AssessmentRound::create(['assessment_id'=>$ass->id,'round_no'=>$i,'due_at'=>$i===1? ($data['first_due_at'] ?? null): null,'status'=>'pending']);
    }
    return $ass->load('rounds');
  }
  public function show(Assessment $assessment){ return $assessment->load('template','rounds'); }
  public function update(Request $r, Assessment $assessment){ $assessment->update($r->all()); return $assessment->fresh(); }
  public function destroy(Assessment $assessment){ $assessment->delete(); return response()->noContent(); }

  // Rounds
  public function rounds(Assessment $assessment){ return $assessment->rounds()->with('assignee')->orderBy('round_no')->get(); }
  public function submitResponse(Request $r, AssessmentRound $round){
    $data=$r->validate(['answers_json'=>'required','status'=>'nullable|string']);
    $resp = AssessmentResponse::create([
      'round_id'=>$round->id,
      'submitted_by'=>$r->user()->id ?? null,
      'answers_json'=>$data['answers_json'],
      'status'=>$data['status'] ?? 'submitted',
    ]);
    $round->update(['status'=>'submitted']);
    return $resp;
  }
  public function responses(AssessmentRound $round){ return $round->responses()->with('submitter')->orderByDesc('id')->get(); }
  public function setRoundStatus(Request $r, AssessmentRound $round){ $round->update($r->validate(['status'=>'required|string'])); return $round->fresh(); }
}
