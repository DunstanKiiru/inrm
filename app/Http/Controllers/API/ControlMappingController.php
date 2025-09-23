<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Control;

class ControlMappingController extends Controller
{
    public function mapRisks(Request $r, Control $control)
    {
        $data = $r->validate([
            'risk_ids'   => 'required|array',
            'risk_ids.*' => 'exists:risks,id',
        ]);

        $control->risks()->syncWithoutDetaching($data['risk_ids']);

        return $control->risks()
            ->withPivot(['effectiveness_rating', 'residual_impact'])
            ->get();
    }

    public function risks(Control $control)
    {
        return $control->risks()
            ->with(['category', 'owner'])
            ->get();
    }
}
