<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use App\Models\Dashboard;
use App\Models\Kpi;
use App\Models\BoardPack;

class ExportController extends Controller {
  public function boardPackPdf(Request $r){
    $dashboardId = (int)$r->query('dashboard_id');
    $from = $r->query('from'); $to = $r->query('to');
    $dashboard = Dashboard::findOrFail($dashboardId);
    $data = app(\App\Http\Controllers\API\DashboardController::class)->show($dashboard);
    $html = View::make('exports.board_pack', ['dashboard'=>$dashboard,'data'=>$data,'from'=>$from,'to'=>$to])->render();

    if(!class_exists('Dompdf\Dompdf')){
      return response()->json(['error'=>'dompdf/dompdf not installed. Run composer require dompdf/dompdf.'], 422);
    }
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4','portrait');
    $dompdf->render();
    $output = $dompdf->output();
    $filename = 'board-pack-'.($dashboard->slug ?? $dashboard->id).'.pdf';
    return response($output,200,[ 'Content-Type'=>'application/pdf', 'Content-Disposition'=>"attachment; filename=\"$filename\"" ]);
  }

  public function dashboardCsv(Request $r){
    $dashboardId = (int)$r->query('dashboard_id');
    $dashboard = Dashboard::findOrFail($dashboardId);
    $data = app(\App\Http\Controllers\API\DashboardController::class)->show($dashboard);
    $csv = fopen('php://temp','r+');
    fputcsv($csv, ['Widget','KPI','Timestamp','Value']);
    foreach(($data['resolved']??[]) as $row){
      if(isset($row['kpi'])){
        foreach($row['series'] as $pt){
          fputcsv($csv, [$row['widget']->title, $row['kpi']->title, $pt->ts, $pt->value]);
        }
      } elseif(isset($row['data'])){
        foreach($row['data'] as $series){
          foreach($series['series'] as $pt){
            fputcsv($csv, [$row['widget']->title, $series['title'], $pt->ts, $pt->value]);
          }
        }
      }
    }
    rewind($csv); $out = stream_get_contents($csv); fclose($csv);
    $filename = 'dashboard-'.($dashboard->slug ?? $dashboard->id).'.csv';
    return response($out,200,[ 'Content-Type'=>'text/csv', 'Content-Disposition'=>"attachment; filename=\"$filename\"" ]);
  }

  public function dashboardXlsx(Request $r){
    if(!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')){
      return response()->json(['error'=>'XLSX export requires phpoffice/phpspreadsheet. Install it or use CSV endpoint.'], 422);
    }
    // For brevity, you can mirror CSV into XLSX here using PhpSpreadsheet in your app.
  }
}
