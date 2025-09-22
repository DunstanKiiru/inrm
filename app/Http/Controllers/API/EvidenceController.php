<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Evidence;
use App\Services\ClamavScanner;
class EvidenceController extends Controller {
  public function index(Request $r){
    $r->validate(['entity_type'=>'required','entity_id'=>'required|integer']);
    return Evidence::where('entity_type',$r->entity_type)->where('entity_id',$r->entity_id)->orderByDesc('id')->get();
  }
  public function store(Request $r, ClamavScanner $scanner){
    $r->validate(['entity_type'=>'required','entity_id'=>'required|integer','file'=>'required|file|max:51200']);
    $file=$r->file('file'); $status=$scanner->scan($file->getRealPath()); if($status==='infected') return response()->json(['message'=>'Malware detected'],422);
    $disk=config('evidence.disk'); $path=$file->store('evidence/'.$r->entity_type.'/'.$r->entity_id,$disk);
    $sha=hash_file('sha256',$file->getRealPath());
    return Evidence::create(['entity_type'=>$r->entity_type,'entity_id'=>$r->entity_id,'filename'=>$file->getClientOriginalName(),'storage_disk'=>$disk,'storage_path'=>$path,'size'=>$file->getSize(),'mime'=>$file->getClientMimeType(),'sha256'=>$sha,'uploaded_by'=>optional($r->user())->id,'scanned_status'=>$status]);
  }
}