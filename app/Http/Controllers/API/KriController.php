<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Kri;
use App\Models\KriReading;
use App\Models\KriBreach;

class KriController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GENERIC KRI (ELOQUENT, entity-based)
    |--------------------------------------------------------------------------
    */
    public function index(Request $r)
    {
        $q = Kri::query()->orderBy('title');
        if ($r->filled('entity_type')) $q->where('entity_type', $r->entity_type);
        if ($r->filled('entity_id')) $q->where('entity_id', $r->entity_id);
        return $q->paginate(20);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => 'required',
            'description' => 'nullable',
            'entity_type' => 'required|in:risk,org_unit',
            'entity_id' => 'required|integer',
            'unit' => 'nullable',
            'cadence' => 'required|in:weekly,monthly,quarterly',
            'target' => 'nullable|numeric',
            'warn_threshold' => 'nullable|numeric',
            'alert_threshold' => 'nullable|numeric',
            'direction' => 'required|in:higher_is_better,lower_is_better'
        ]);
        return Kri::create($data);
    }

    public function show(Kri $kri)
    {
        return $kri->load('readings','breaches');
    }

    public function update(Request $r, Kri $kri)
    {
        $kri->update($r->all());
        return $kri->fresh();
    }

    public function destroy(Kri $kri)
    {
        $kri->delete();
        return response()->noContent();
    }

    // ---- Readings ----
    public function readings(Kri $kri)
    {
        return $kri->readings()->orderBy('collected_at','desc')->limit(100)->get();
    }

    public function addReading(Request $r, Kri $kri)
    {
        $data = $r->validate([
            'value' => 'required|numeric',
            'collected_at' => 'nullable|date',
            'source' => 'nullable|string'
        ]);
        return $kri->readings()->create($data);
    }

    // ---- Breaches ----
    public function breaches(Kri $kri)
    {
        return $kri->breaches()->orderByDesc('id')->limit(50)->get();
    }

    /*
    |--------------------------------------------------------------------------
    | VENDOR-SPECIFIC KRI (TPR, table-based)
    |--------------------------------------------------------------------------
    */
    public function vendorDefs($vendorId)
    {
        return DB::table('tpr_vendor_kri_defs')
            ->where('vendor_id', $vendorId)
            ->orderBy('code')
            ->get();
    }

    public function vendorCreateDef($vendorId, Request $r)
    {
        $data = $r->validate([
            'code'=>'required',
            'name'=>'required',
            'unit'=>'nullable',
            'green_max'=>'nullable|numeric',
            'amber_max'=>'nullable|numeric'
        ]);
        $id = DB::table('tpr_vendor_kri_defs')->insertGetId(array_merge($data, [
            'vendor_id'=>$vendorId,
            'created_at'=>now(),
            'updated_at'=>now()
        ]));
        return DB::table('tpr_vendor_kri_defs')->where('id',$id)->first();
    }

    public function vendorTrend($vendorId, Request $r)
    {
        $days = (int)$r->input('days', 90);
        $start = now()->subDays($days)->toDateString();
        $rows = DB::select("
            SELECT DATE(measured_at) as t, COUNT(*) as alerts
            FROM tpr_vendor_kri_measures
            WHERE vendor_id = ? AND measured_at >= ? AND status IN ('alert','breach')
            GROUP BY DATE(measured_at) ORDER BY t ASC
        ", [$vendorId, $start]);

        return array_map(fn($row) => [
            't' => $row->t,
            'v' => (int)$row->alerts
        ], $rows);
    }
}
