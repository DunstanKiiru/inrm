<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DashboardDigest;
use App\Models\Dashboard;

class DigestController extends Controller {
  public function sendNow(Request $r){
    $emails = $r->validate(['emails'=>'required|array'])['emails'];
    $dashboardId = (int)$r->query('dashboard_id');
    $dashboard = Dashboard::findOrFail($dashboardId);
    foreach($emails as $to){
      Mail::to($to)->send(new DashboardDigest($dashboard));
    }
    return ['ok'=>true, 'sent'=>count($emails)];
  }
}
