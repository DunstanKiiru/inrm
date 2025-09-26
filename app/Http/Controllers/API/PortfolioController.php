<?php
namespace App\Http\Controllers\Api;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortfolioController extends Controller
{
    // High-level portfolio view for exec/board dashboards
    // GET /api/tpr/portfolio/overview?days=90
    public function overview(Request $r)
    {
        $days = (int)$r->input('days', 90);
        $since = now()->subDays($days)->toDateString();

        $counts = DB::table('tpr_vendors')->select('tier', DB::raw('COUNT(*) as n'))->groupBy('tier')->get();
        $topRisky = DB::table('tpr_vendors')->select('id','code','name','residual_score')->orderByDesc('residual_score')->limit(10)->get();

        $kriAlerts = DB::table('tpr_vendor_kri_measures')->select('vendor_id', DB::raw('COUNT(*) as alerts'))
            ->where('measured_at','>=',$since)->whereIn('status',['alert','breach'])->groupBy('vendor_id')->orderByDesc('alerts')->limit(10)->get();

        $slaBreaches = DB::table('tpr_vendor_sla_measures')->select('vendor_id', DB::raw('COUNT(*) as breaches'))
            ->where('measured_at','>=',$since)->where('status','breach')->groupBy('vendor_id')->orderByDesc('breaches')->limit(10)->get();

        return ['since'=>$since,'counts_by_tier'=>$counts,'top_risky'=>$topRisky,'top_kri_alerts'=>$kriAlerts,'top_sla_breaches'=>$slaBreaches];
    }

    // GET /api/tpr/portfolio/top?metric=kri_alerts|sla_breaches|residual_score&limit=20
    public function top(Request $r)
    {
        $metric = $r->input('metric','residual_score');
        $limit = (int)$r->input('limit', 20);
        if ($metric === 'residual_score') {
            return DB::table('tpr_vendors')->select('id','code','name','residual_score')->orderByDesc('residual_score')->limit($limit)->get();
        }
        $since = now()->subDays((int)$r->input('days', 90))->toDateString();
        if ($metric === 'kri_alerts') {
            return DB::table('tpr_vendor_kri_measures')->select('vendor_id', DB::raw('COUNT(*) as alerts'))
                ->where('measured_at','>=',$since)->whereIn('status',['alert','breach'])->groupBy('vendor_id')->orderByDesc('alerts')->limit($limit)->get();
        }
        if ($metric === 'sla_breaches') {
            return DB::table('tpr_vendor_sla_measures')->select('vendor_id', DB::raw('COUNT(*) as breaches'))
                ->where('measured_at','>=',$since)->where('status','breach')->groupBy('vendor_id')->orderByDesc('breaches')->limit($limit)->get();
        }
        return [];
    }
}
