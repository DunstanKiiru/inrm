<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KriBreachController extends Controller
{
    public function active(Request $r){
        $level = $r->query('level');
        $sinceDays = (int)($r->query('since_days', 90));
        $limit = (int)($r->query('limit', 20));

        $params = [$sinceDays];
        $levelFilter = '';
        if($level){ $levelFilter = ' AND b.level = ? '; $params[] = $level; }

        $sql = "
        SELECT b.id as breach_id, b.level, b.message, b.created_at,
               k.id as kri_id, k.title as kri_title, k.entity_type, k.entity_id,
               k.unit, k.direction, k.target, k.warn_threshold, k.alert_threshold,
               r.value as reading_value, r.collected_at as reading_at
        FROM kri_breaches b
        JOIN kris k ON k.id = b.kri_id
        LEFT JOIN kri_readings r ON r.id = b.reading_id
        WHERE b.acknowledged_at IS NULL
          AND b.created_at >= now() - INTERVAL '? days' "
          . $levelFilter .
        " ORDER BY b.created_at DESC
          LIMIT ?";

        $params[] = $limit;
        $rows = DB::select($sql, $params);
        return $rows;
    }

    public function acknowledge(Request $r, int $breach){
        DB::table('kri_breaches')->where('id',$breach)->update([
            'acknowledged_at' => now()
        ]);
        return response()->json(['ok'=>true]);
    }
}
