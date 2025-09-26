<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TprRuleSuppression;
use Illuminate\Http\Request;

class SuppressionsController extends Controller
{
    public function index(Request $request)
    {
        $query = TprRuleSuppression::query();

        if ($request->filled('rule_id')) {
            $query->where('rule_id', $request->rule_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        return $query->paginate(50);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rule_id' => 'nullable|exists:tpr_rules,id',
            'vendor_id' => 'nullable|exists:tpr_vendors,id',
            'until' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $suppression = TprRuleSuppression::create($data);

        return response()->json($suppression, 201);
    }

    public function destroy($id)
    {
        $suppression = TprRuleSuppression::find($id);

        if (!$suppression) {
            return response()->json(['error' => 'Suppression not found'], 404);
        }

        $suppression->delete();

        return response()->json(['message' => 'Suppression deleted']);
    }
}
