<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kpi;
use App\Models\KpiReading;

class KpiController extends Controller {
  public function list(){ return Kpi::orderBy('title')->get(); }
  public function timeseries(Request $r, Kpi $kpi){
    $from = $r->query('from'); $to = $r->query('to');
    $q = $kpi->readings()->orderBy('ts');
    if($from) $q->where('ts','>=',$from);
    if($to) $q->where('ts','<=',$to);
    return $q->get();
  }
  public function create(Request $r){
    $data=$r->validate(['key'=>'required','title'=>'required','unit'=>'nullable','target'=>'nullable|numeric','direction'=>'nullable|in:up,down','owner_id'=>'nullable|integer']);
    return Kpi::create($data);
  }
  public function addReading(Request $r, Kpi $kpi){
    $data=$r->validate(['ts'=>'nullable|date','value'=>'required|numeric']);
    if(empty($data['ts'])) $data['ts'] = now();
    return $kpi->readings()->create($data);
  }
}
