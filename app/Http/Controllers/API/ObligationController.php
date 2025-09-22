<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Obligation;
use Illuminate\Support\Facades\DB;

class ObligationController extends Controller {
  public function index(Request $r){
    $q = Obligation::with('requirements')->orderByDesc('id');
    if($r->filled('jurisdiction')) $q->where('jurisdiction',$r->jurisdiction);
    if($r->filled('owner_id')) $q->where('owner_id',$r->owner_id);
    if($r->filled('search')) $q->where('title','like','%'.$r->search.'%');
    return $q->paginate(20);
  }
  public function store(Request $r){
    $data=$r->validate([
      'title'=>'required','jurisdiction'=>'nullable','source_doc_url'=>'nullable',
      'summary'=>'nullable','effective_date'=>'nullable|date','review_cycle'=>'nullable',
      'owner_id'=>'nullable|integer','requirement_ids'=>'array'
    ]);
    $o = Obligation::create($data);
    if(!empty($data['requirement_ids'])){
      $o->requirements()->sync($data['requirement_ids']);
    }
    return $o->load('requirements');
  }
  public function update(Request $r, Obligation $obligation){
    $obligation->update($r->all());
    if($r->has('requirement_ids')) $obligation->requirements()->sync($r->requirement_ids ?? []);
    return $obligation->load('requirements');
  }
  public function destroy(Obligation $obligation){ $obligation->delete(); return response()->noContent(); }
}
