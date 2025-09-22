<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Framework;
use App\Models\Requirement;

class FrameworkController extends Controller {
  public function index(){
    return Framework::withCount('requirements')->orderBy('title')->get();
  }
  public function show(Framework $framework){
    $reqs = Requirement::where('framework_id',$framework->id)->orderBy('code')->get();
    return ['framework'=>$framework, 'requirements'=>$reqs];
  }
  public function mapControl(Request $r, Framework $framework, Requirement $requirement){
    $data=$r->validate(['control_id'=>'required|integer']);
    DB::table('control_requirement')->updateOrInsert([
      'requirement_id'=>$requirement->id,
      'control_id'=>$data['control_id']
    ], ['updated_at'=>now(),'created_at'=>now()]);
    return response()->json(['ok'=>true]);
  }
  public function unmapControl(Request $r, Framework $framework, Requirement $requirement, int $controlId){
    DB::table('control_requirement')->where(['requirement_id'=>$requirement->id,'control_id'=>$controlId])->delete();
    return response()->json(['ok'=>true]);
  }
}
