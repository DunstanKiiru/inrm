<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ControlAnalyticsController extends Controller
{
    public function effectivenessByCategory(Request $r){
        $months = (int)($r->query('window', 6));
        $since = now()->subMonths($months);
        $categoryId = $r->query('category_id');
        $ownerId = $r->query('owner_id');

        $params = [$since];
        $ctlFilter = '';
        if ($categoryId){ $ctlFilter .= ' AND c.category_id = ? '; $params[] = (int)$categoryId; }
        if ($ownerId){    $ctlFilter .= ' AND c.owner_id = ? ';    $params[] = (int)$ownerId; }

        $sql = "
        WITH latest_exec AS (
          SELECT e.*, p.control_id,
                 ROW_NUMBER() OVER (PARTITION BY e.plan_id ORDER BY e.executed_at DESC NULLS LAST, e.id DESC) rn
          FROM control_test_executions e
          JOIN control_test_plans p ON p.id = e.plan_id
          WHERE (e.executed_at IS NULL OR e.executed_at >= ?) 
        )
        SELECT cc.name as category,
               SUM(CASE WHEN le.result = 'pass' THEN 1 ELSE 0 END) AS pass_count,
               SUM(CASE WHEN le.result = 'partial' THEN 1 ELSE 0 END) AS partial_count,
               SUM(CASE WHEN le.result = 'fail' THEN 1 ELSE 0 END) AS fail_count,
               COUNT(*) AS total
        FROM latest_exec le
        JOIN controls c ON c.id = le.control_id
        LEFT JOIN control_categories cc ON cc.id = c.category_id
        WHERE le.rn = 1 " . $ctlFilter . "
        GROUP BY cc.name
        ORDER BY total DESC NULLS LAST
        ";
        return DB::select($sql, $params);
    }

    public function effectivenessByOwner(Request $r){
        $months = (int)($r->query('window', 6));
        $since = now()->subMonths($months);
        $categoryId = $r->query('category_id');
        $ownerId = $r->query('owner_id');

        $params = [$since];
        $ctlFilter = '';
        if ($categoryId){ $ctlFilter .= ' AND c.category_id = ? '; $params[] = (int)$categoryId; }
        if ($ownerId){    $ctlFilter .= ' AND c.owner_id = ? ';    $params[] = (int)$ownerId; }

        $sql = "
        WITH latest_exec AS (
          SELECT e*, p.control_id,
                 ROW_NUMBER() OVER (PARTITION BY e.plan_id ORDER BY e.executed_at DESC NULLS LAST, e.id DESC) rn
          FROM control_test_executions e
          JOIN control_test_plans p ON p.id = e.plan_id
          WHERE (e.executed_at IS NULL OR e.executed_at >= ?) 
        )
        SELECT u.name as owner,
               SUM(CASE WHEN le.result = 'pass' THEN 1 ELSE 0 END) AS pass_count,
               SUM(CASE WHEN le.result = 'partial' THEN 1 ELSE 0 END) AS partial_count,
               SUM(CASE WHEN le.result = 'fail' THEN 1 ELSE 0 END) AS fail_count,
               COUNT(*) AS total
        FROM latest_exec le
        JOIN controls c ON c.id = le.control_id
        LEFT JOIN users u ON u.id = c.owner_id
        WHERE le.rn = 1 " . $ctlFilter . "
        GROUP BY u.name
        ORDER BY total DESC NULLS LAST, owner ASC
        ";
        return DB::select($sql, $params);
    }

    public function passrateSeries(Request $r){
        $months = (int)($r->query('window', 6));
        $controlId = $r->query('control_id');
        $since = now()->startOfMonth()->subMonths($months - 1);
        $params = [$since];
        $controlFilter = '';
        if ($controlId) { $controlFilter = ' AND p.control_id = ? '; $params[] = (int)$controlId; }

        $sql = "
          SELECT to_char(date_trunc('month', e.executed_at), 'YYYY-MM') as ym,
                 SUM(CASE WHEN e.result='pass' THEN 1 ELSE 0 END)::int as pass_count,
                 COUNT(*)::int as total_count
          FROM control_test_executions e
          JOIN control_test_plans p ON p.id = e.plan_id
          WHERE (e.executed_at IS NOT NULL AND e.executed_at >= ?) " . $controlFilter . "
          GROUP BY 1 ORDER BY 1 ASC ";
        $rows = DB::select($sql, $params);
        $out = [];
        for ($i=0; $i<$months; $i++){
            $ym = now()->startOfMonth()->subMonths($months - 1 - $i)->format('Y-m');
            $found = collect($rows)->firstWhere('ym', $ym);
            $pass = $found->pass_count ?? 0;
            $tot  = $found->total_count ?? 0;
            $rate = $tot ? round($pass*100/$tot, 1) : 0.0;
            $out[] = ['ym'=>$ym, 'pass_rate'=>$rate, 'pass_count'=>$pass, 'total_count'=>$tot];
        }
        return $out;
    }

    public function owners(){
        $rows = DB::select("
          SELECT DISTINCT u.id, u.name
          FROM controls c
          JOIN users u ON u.id = c.owner_id
          ORDER BY u.name
        ");
        return $rows;
    }

    public function recentExecutions(Request $r, int $control){
        $limit = (int)($r->query('limit', 10));
        $sql = "
          SELECT e.id, e.result, e.effectiveness_rating, e.comments, e.executed_at, u.name as executed_by
          FROM control_test_executions e
          JOIN control_test_plans p ON p.id = e.plan_id
          LEFT JOIN users u ON u.id = e.executed_by
          WHERE p.control_id = ?
          ORDER BY e.executed_at DESC NULLS LAST, e.id DESC
          LIMIT ?
        ";
        return DB::select($sql, [$control, $limit]);
    }
}
