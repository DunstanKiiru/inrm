<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VendorsController extends Controller
{
    public function index(Request $r)
    {
        $q = DB::table('tpr_vendors');

        if ($r->filled('status')) {
            $q->where('status', $r->status);
        }
        if ($r->filled('tier')) {
            $q->where('tier', $r->tier);
        }
        if ($r->filled('search')) {
            $s = '%' . $r->search . '%';
            $q->where(function ($w) use ($s) {
                $w->where('code', 'like', $s)
                  ->orWhere('name', 'like', $s)
                  ->orWhere('category', 'like', $s);
            });
        }

        return $q->orderBy('name')->paginate(50);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'        => 'required',
            'name'        => 'required',
            'tier'        => 'nullable',
            'criticality' => 'nullable',
            'status'      => 'nullable',
            'category'    => 'nullable',
        ]);

        $id = DB::table('tpr_vendors')->insertGetId(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return DB::table('tpr_vendors')->where('id', $id)->first();
    }

    public function show($id)
    {
        $v = DB::table('tpr_vendors')->where('id', $id)->orWhere('code', $id)->first();
        if (!$v) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $contacts = DB::table('tpr_vendor_contacts')->where('vendor_id', $v->id)->get();
        $risks = DB::table('tpr_vendor_risk as vr')
            ->join('risks as r', 'r.id', '=', 'vr.risk_id')
            ->select('r.id', 'r.code', 'r.title')
            ->where('vr.vendor_id', $v->id)
            ->get();

        return [
            'vendor'   => $v,
            'contacts' => $contacts,
            'risks'    => $risks,
        ];
    }

    public function update($id, Request $r)
    {
        $v = DB::table('tpr_vendors')->where('id', $id)->orWhere('code', $id)->first();
        if (!$v) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $data = $r->validate([
            'name'            => 'nullable',
            'tier'            => 'nullable',
            'criticality'     => 'nullable',
            'status'          => 'nullable',
            'category'        => 'nullable',
            'inherent_score'  => 'nullable|numeric',
            'residual_score'  => 'nullable|numeric',
        ]);

        DB::table('tpr_vendors')->where('id', $v->id)->update(array_merge($data, [
            'updated_at' => now(),
        ]));

        return DB::table('tpr_vendors')->where('id', $v->id)->first();
    }

    public function setRiskRating($id, Request $r)
    {
        $v = DB::table('tpr_vendors')->where('id', $id)->orWhere('code', $id)->first();
        if (!$v) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $data = $r->validate([
            'inherent_score' => 'nullable|numeric',
            'residual_score' => 'nullable|numeric',
        ]);

        DB::table('tpr_vendors')->where('id', $v->id)->update(array_merge($data, [
            'updated_at' => now(),
        ]));

        return ['ok' => true];
    }

    public function overview($id, Request $r)
    {
        $v = DB::table('tpr_vendors')->where('id', $id)->orWhere('code', $id)->first();
        if (!$v) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $days  = (int) $r->input('days', 90);
        $start = now()->subDays($days)->toDateString();

        // KRI sparkline (alerts/breaches per day)
        $kri = DB::select(
            "
            SELECT DATE(measured_at) as d, COUNT(*) as n
            FROM tpr_vendor_kri_measures
            WHERE vendor_id = ?
              AND measured_at >= ?
              AND status IN ('alert','breach')
            GROUP BY DATE(measured_at)
            ORDER BY d ASC
            ",
            [$v->id, $start]
        );

        // Assessment history
        $assess = DB::table('tpr_assessments')
            ->select('completed_at as t', 'score', 'residual_score')
            ->where('vendor_id', $v->id)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at')
            ->get();

        // SLA breaches
        $sla = DB::table('tpr_vendor_sla_measures')
            ->where('vendor_id', $v->id)
            ->where('measured_at', '>=', $start)
            ->where('status', 'breach')
            ->count();

        // Optional integrations
        $issues   = 0;
        $findings = 0;

        if (Schema::hasTable('issues')) {
            $issues = DB::table('issues')
                ->where('status', 'open')
                ->where('description', 'like', '%Vendor: ' . $v->code . '%')
                ->count();
        }

        if (Schema::hasTable('audit_findings')) {
            $findings = DB::table('audit_findings')
                ->where('status', 'open')
                ->where('description', 'like', '%Vendor: ' . $v->code . '%')
                ->count();
        }

        return [
            'vendor'        => $v,
            'kri_sparkline' => array_map(fn($r) => ['t' => $r->d, 'v' => (int) $r->n], $kri),
            'assessments'   => $assess,
            'sla_breaches'  => $sla,
            'open_issues'   => $issues,
            'open_findings' => $findings,
        ];
    }
}
