<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ControlIssue;
use App\Models\ControlRemediation;

class ControlIssueController extends Controller
{
    public function index(Request $r)
    {
        $q = ControlIssue::with(['control', 'owner'])->orderByDesc('id');

        if ($r->filled('status')) {
            $q->where('status', $r->status);
        }
        if ($r->filled('control_id')) {
            $q->where('control_id', $r->control_id);
        }

        return $q->paginate(20);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'control_id'        => 'required|exists:controls,id',
            'test_execution_id' => 'nullable|exists:control_test_executions,id',
            'description'       => 'required|string',
            'severity'          => 'nullable|string',
            'status'            => 'nullable|string',
            'owner_id'          => 'nullable|exists:users,id',
            'due_date'          => 'nullable|date',
        ]);

        return ControlIssue::create($data);
    }

    public function update(Request $r, ControlIssue $controlIssue)
    {
        $controlIssue->update($r->all());

        return $controlIssue->fresh()->load(['control', 'owner']);
    }

    public function destroy(ControlIssue $controlIssue)
    {
        $controlIssue->delete();

        return response()->noContent();
    }

    // ---------------- Remediations ----------------

    public function listRemediations(ControlIssue $controlIssue)
    {
        return $controlIssue->remediations()
            ->with('assignee')
            ->orderByDesc('id')
            ->get();
    }

    public function addRemediation(Request $r, ControlIssue $controlIssue)
    {
        $data = $r->validate([
            'description' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
            'status'      => 'nullable|string',
        ]);

        $data['issue_id'] = $controlIssue->id;

        return ControlRemediation::create($data);
    }

    public function updateRemediation(Request $r, ControlRemediation $remediation)
    {
        $remediation->update($r->all());

        return $remediation->fresh()->load('assignee');
    }

    public function destroyRemediation(ControlRemediation $remediation)
    {
        $remediation->delete();

        return response()->noContent();
    }
}
