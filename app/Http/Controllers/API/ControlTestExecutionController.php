<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ControlTestPlan;
use App\Models\ControlTestExecution;

class ControlTestExecutionController extends Controller
{
    /**
     * Execute a control test plan and create a new execution record.
     */
    public function execute(Request $request, ControlTestPlan $plan)
    {
        $data = $request->validate([
            'executed_at' => 'nullable|date',
            'result' => 'required|in:pass,fail,partial',
            'effectiveness_rating' => 'nullable|in:Effective,Partial,Ineffective',
            'comments' => 'nullable|string',
        ]);

        // Ensure the user is authenticated
        $data['executed_by'] = $request->user()->id;

        // Create the execution record
        $execution = $plan->executions()->create($data);

        // Auto-advance next_due date based on frequency
        if ($plan->next_due && $plan->frequency) {
            try {
                $nextDue = match ($plan->frequency) {
                    'monthly' => now()->addMonth(),
                    'quarterly' => now()->addMonths(3),
                    'annual' => now()->addYear(),
                    default => null,
                };

                if ($nextDue) {
                    $plan->update(['next_due' => $nextDue]);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json(
            $execution->load(['plan.control', 'executor']),
            201
        );
    }

    /**
     * Show details of a specific execution.
     */
    public function show(ControlTestExecution $execution)
    {
        return response()->json(
            $execution->load(['plan.control', 'executor'])
        );
    }
}
