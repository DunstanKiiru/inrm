<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Control;
use App\Models\ControlTestPlan;

class ControlTestPlanController extends Controller
{
    /**
     * List all test plans for a given control.
     */
    public function index(Control $control)
    {
        return response()->json(
            $control->testPlans()
                ->with('assignee')
                ->orderByDesc('id')
                ->get()
        );
    }

    /**
     * Create a new test plan for a control.
     */
    public function store(Request $request, Control $control)
    {
        $data = $request->validate([
            'test_type'   => 'required|in:design,operating',
            'frequency'   => 'required|string',
            'next_due'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status'      => 'nullable|string',
            'scope'       => 'nullable|string',
            'methodology' => 'nullable|string',
        ]);

        $data['control_id'] = $control->id;

        $plan = ControlTestPlan::create($data);

        return response()->json($plan, 201);
    }

    /**
     * Update an existing test plan.
     */
    public function update(Request $request, ControlTestPlan $plan)
    {
        $data = $request->validate([
            'test_type'   => 'sometimes|in:design,operating',
            'frequency'   => 'sometimes|string',
            'next_due'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'status'      => 'nullable|string',
            'scope'       => 'nullable|string',
            'methodology' => 'nullable|string',
        ]);

        $plan->update($data);

        return response()->json(
            $plan->fresh()->load('assignee')
        );
    }

    /**
     * Delete a test plan.
     */
    public function destroy(ControlTestPlan $plan)
    {
        $plan->delete();
        return response()->noContent();
    }
}
