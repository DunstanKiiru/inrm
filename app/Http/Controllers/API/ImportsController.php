<?php
namespace App\Http\Controllers\Api;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportsController extends Controller
{
    // Accepts CSV via multipart 'file' OR raw text body 'csv' field.
    // Columns: kri_code,kri_name,measured_at,value,status
    public function kriCsv($vendorId, Request $r)
    {
        $csv = null;
        if ($r->hasFile('file')) { $csv = file_get_contents($r->file('file')->getRealPath()); }
        elseif ($r->filled('csv')) { $csv = $r->input('csv'); }
        else return response()->json(['error'=>'Provide CSV via file or csv text field'], 422);

        $lines = preg_split('/\r?\n/', trim($csv));
        $header = null; $n=0;
        foreach ($lines as $line) {
            if ($line === '') continue;
            $cols = str_getcsv($line);
            if (!$header) { $header = array_map('trim', $cols); continue; }
            $row = array_combine($header, $cols);
            if (!$row) continue;
            DB::table('tpr_vendor_kri_measures')->insert([
                'vendor_id'=>$vendorId,
                'kri_id'=> null,
                'kri_code'=> $row['kri_code'] ?? '',
                'measured_at'=> $row['measured_at'] ?? now(),
                'value'=> isset($row['value']) ? (float)$row['value'] : null,
                'status'=> $row['status'] ?? null,
                'meta'=> json_encode(['kri_name'=>$row['kri_name'] ?? null]),
                'created_at'=> now(), 'updated_at'=> now()
            ]);
            $n++;
            if (in_array($row['status'] ?? '', config('inrm_tpr.kri.alert_statuses', ['alert','breach']))) {
                $this->emitRim('TPR_KRI_ALERT', $vendorId, ['kri_code'=>$row['kri_code'] ?? null,'value'=>$row['value'] ?? null]);
            }
        }
        return ['ingested'=>$n];
    }

    protected function emitRim(string $type, int $vendorId, array $metrics = []): void
    {
        if (!config('inrm_tpr.rim_events_enabled')) return;
        if (!\Illuminate\Support\Facades\Schema::hasTable('rim_events')) return;
        \Illuminate\Support\Facades\DB::table('rim_events')->insert([
            'type'=>$type,'severity'=>'medium','risk_id'=>null,
            'message'=>'TPR event '.$type.' vendor_id='.$vendorId,
            'occurred_at'=> now(), 'metrics'=> json_encode(array_merge(['vendor_id'=>$vendorId], $metrics)),
            'created_at'=> now(), 'updated_at'=> now()
        ]);
    }
}
