<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Control;
use App\Models\ControlTestPlan;

class ControlTestPlanController extends Controller
{
    public function index(Control $control){ return $control->testPlans()->with('assignee')->orderByDesc('id')->get(); }
    public function store(Request $r, Control $control){
        $data=$r->validate([
            'test_type'=>'required|in:design,operating',
            'frequency'=>'required|string',
            'next_due'=>'nullable|date',
            'assigned_to'=>'nullable|exists:users,id',
            'status'=>'nullable|string',
            'scope'=>'nullable|string',
            'methodology'=>'nullable|string'
        ]);
        $data['control_id']=$control->id;
        return ControlTestPlan::create($data);
    }
    public function update(Request $r, ControlTestPlan $plan){
        $plan->update($r->all());
        return $plan->fresh()->load('assignee');
    }
    public function destroy(ControlTestPlan $plan){ $plan->delete(); return response()->noContent(); }
}
