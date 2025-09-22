<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KriReadingBatchController extends Controller
{
    public function batch(Request $r){
        $ids = $r->query('kri_ids', '');
        $limit = (int)($r->query('limit', 6));
        if(!$ids){ return []; }
        $arr = array_values(array_filter(array_map('intval', explode(',', $ids))));
        if(empty($arr)){ return []; }
        // Use window function to grab top N per KRI, then reorder ascending for chart
        $in = implode(',', array_fill(0, count($arr), '?'));
        $params = array_merge($arr, [$limit]);
        $sql = "
          WITH ranked AS (
            SELECT r.kri_id, r.value, r.collected_at,
                   ROW_NUMBER() OVER (PARTITION BY r.kri_id ORDER BY r.collected_at DESC NULLS LAST, r.id DESC) rn
            FROM kri_readings r
            WHERE r.kri_id IN ($in)
          )
          SELECT kri_id, value, collected_at
          FROM ranked
          WHERE rn <= ?
          ORDER BY kri_id ASC, collected_at ASC NULLS FIRST, value ASC
        ";
        $rows = DB::select($sql, $params);
        $out = [];
        foreach($rows as $row){
            $kid = (int)$row->kri_id;
            if(!isset($out[$kid])) $out[$kid] = [];
            $out[$kid][] = ['value'=>(float)$row->value, 'collected_at'=>$row->collected_at];
        }
        return $out;
    }
}
