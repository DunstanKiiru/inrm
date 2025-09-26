<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SlaController extends Controller
{
    public function defs($vendorId)
    {
        return DB::table('tpr_vendor_sla_defs')
            ->where('vendor_id', $vendorId)
            ->orderBy('code')
            ->get();
    }

    public function createDef($vendorId, Request $r)
    {
        $data = $r->validate([
            'code'   => 'required',
            'name'   => 'required',
            'unit'   => 'nullable',
            'target' => 'nullable|numeric'
        ]);

        $id = DB::table('tpr_vendor_sla_defs')->insertGetId(array_merge($data, [
            'vendor_id'  => $vendorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return DB::table('tpr_vendor_sla_defs')->where('id', $id)->first();
    }

    public function trend($vendorId, Request $r)
    {
        $days  = (int) $r->input('days', 90);
        $start = now()->subDays($days)->toDateString();

        $rows = DB::select(
            "
            SELECT DATE(measured_at) as t, COUNT(*) as breaches
            FROM tpr_vendor_sla_measures
            WHERE vendor_id = ?
              AND measured_at >= ?
              AND status = 'breach'
            GROUP BY DATE(measured_at)
            ORDER BY t ASC
            ",
            [$vendorId, $start]
        );

        return array_map(fn($row) => [
            't' => $row->t,
            'v' => (int) $row->breaches,
        ], $rows);
    }

    public function ingest($vendorId, Request $r)
    {
        $data = $r->validate([
            'rows' => 'array', // expected: [{sla_code,value,measured_at?,status?,meta?}]
        ]);

        $rows = $data['rows'] ?? [];
        $n    = 0;

        foreach ($rows as $row) {
            DB::table('tpr_vendor_sla_measures')->insert([
                'vendor_id'  => $vendorId,
                'sla_id'     => null,
                'sla_code'   => $row['sla_code'],
                'measured_at'=> $row['measured_at'] ?? now(),
                'value'      => $row['value'] ?? null,
                'status'     => $row['status'] ?? null,
                'meta'       => isset($row['meta']) ? json_encode($row['meta']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $n++;

            if (($row['status'] ?? null) === 'breach') {
                $this->emitRim(
                    'TPR_SLA_BREACH',
                    $vendorId,
                    ['sla_code' => $row['sla_code'], 'value' => $row['value'] ?? null]
                );
            }
        }

        return ['ingested' => $n];
    }

    protected function emitRim(string $type, int $vendorId, array $metrics = []): void
    {
        if (!config('inrm_tpr.rim_events_enabled')) {
            return;
        }

        if (!Schema::hasTable('rim_events')) {
            return;
        }

        DB::table('rim_events')->insert([
            'type'       => $type,
            'severity'   => 'medium',
            'risk_id'    => null,
            'message'    => 'TPR event ' . $type . ' vendor_id=' . $vendorId,
            'occurred_at'=> now(),
            'metrics'    => json_encode(array_merge(['vendor_id' => $vendorId], $metrics)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
