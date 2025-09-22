<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dashboard;
use App\Models\ReportWidget;
use App\Models\Kpi;
use App\Models\KpiReading;

class DashboardController extends Controller {
  public function list(Request $r){
    $q = Dashboard::query();
    if($r->filled('role')) $q->where('role',$r->role);
    return $q->orderBy('title')->get();
  }
  public function show(Dashboard $dashboard){
    $widgets = $dashboard->widgets()->orderBy('order_index')->get();
    $resolved = [];
    foreach($widgets as $w){
      $cfg = $w->config_json ?? [];
      if($w->type==='kpi'){
        $kpi = Kpi::where('key',$cfg['kpi_key'] ?? '')->first();
        if($kpi){
          $latest = $kpi->readings()->orderByDesc('ts')->first();
          $series = $kpi->readings()->orderBy('ts')->limit(24)->get(['ts','value']);
          $resolved[] = ['widget'=>$w, 'kpi'=>$kpi, 'latest'=>$latest, 'series'=>$series];
        }
      } elseif($w->type==='chart'){
        // Fetch series for list of KPI keys
        $keys = $cfg['kpi_keys'] ?? [];
        $data=[];
        foreach($keys as $k){
          $kpi = Kpi::where('key',$k)->first();
          if(!$kpi) continue;
          $data[] = [
            'key'=>$kpi->key,'title'=>$kpi->title,
            'series'=>$kpi->readings()->orderBy('ts')->limit(24)->get(['ts','value'])
          ];
        }
        $resolved[] = ['widget'=>$w, 'data'=>$data];
      } else {
        $resolved[] = ['widget'=>$w];
      }
    }
    return ['dashboard'=>$dashboard, 'widgets'=>$widgets, 'resolved'=>$resolved];
  }
}
