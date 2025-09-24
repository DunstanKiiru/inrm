<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RiskRollupController extends Controller
{
    public function byCategory()
    {
        $rows = DB::select(<<<SQL
            SELECT rc.name AS category,
                   COUNT(*) AS total,
                   SUM(CASE WHEN r.inherent_score BETWEEN 1 AND 5 THEN 1 ELSE 0 END) AS low,
                   SUM(CASE WHEN r.inherent_score BETWEEN 6 AND 12 THEN 1 ELSE 0 END) AS medium,
                   SUM(CASE WHEN r.inherent_score BETWEEN 13 AND 20 THEN 1 ELSE 0 END) AS high,
                   SUM(CASE WHEN r.inherent_score > 20 THEN 1 ELSE 0 END) AS extreme
            FROM risks r
            LEFT JOIN risk_categories rc ON rc.id = r.category_id
            GROUP BY rc.name
            ORDER BY total DESC
        SQL);

        return response()->json($rows);
    }

    public function byOrgUnit()
    {
        $rows = DB::select(<<<SQL
            SELECT ou.name AS org_unit,
                   AVG(r.residual_score) AS avg_residual,
                   COUNT(*) AS risks
            FROM risks r
            LEFT JOIN org_units ou ON ou.id = r.org_unit_id
            GROUP BY ou.name
            ORDER BY avg_residual DESC
        SQL);

        return response()->json($rows);
    }

    public function byOwner()
    {
        $rows = DB::select(<<<SQL
            SELECT u.name AS owner,
                   COUNT(*) AS risks,
                   AVG(r.inherent_score) AS avg_inherent
            FROM risks r
            LEFT JOIN users u ON u.id = r.owner_id
            GROUP BY u.name
            ORDER BY risks DESC
        SQL);

        return response()->json($rows);
    }
}
