<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ControlTestPlan;
use App\Models\ControlTestExecution;

class ControlTestExecutionController extends Controller
{
    public function execute(Request $r, ControlTestPlan $plan){
        $data=$r->validate([
            'executed_at'=>'nullable|date',
            'result'=>'required|in:pass,fail,partial',
            'effectiveness_rating'=>'nullable|in:Effective,Partial,Ineffective',
            'comments'=>'nullable|string'
        ]);
        $data['executed_by'] = $r->user()->id ?? null;
        $exec = $plan->executions()->create($data);
        // simple auto-advance next_due based on frequency (monthly/quarterly/annual)
        if($plan->next_due && $plan->frequency){
            try{
                $next = match($plan->frequency){
                    'monthly' => now()->addMonth(),
                    'quarterly' => now()->addMonths(3),
                    'annual' => now()->addYear(),
                    default => null
                };
                if($next){ $plan->update(['next_due'=>$next]); }
            }catch(\Throwable $e){}
        }
        return $exec;
    }
    public function show(ControlTestExecution $execution){ return $execution->load(['plan.control','executor']); }
}
