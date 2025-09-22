<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Control;
use App\Models\ControlTestPlan;
use App\Models\ControlTestExecution;
use App\Models\User;

class ControlTestingDemoSeed extends Seeder
{
    public function run(): void
    {
        $tester = User::first();

        foreach (Control::limit(4)->get() as $ctrl) {
            $plan = ControlTestPlan::firstOrCreate(
                [
                    'control_id' => $ctrl->id,
                    'test_type'  => 'operating',
                    'frequency'  => 'monthly',
                ],
                [
                    'assigned_to' => $tester?->id,
                    'status'      => 'active',
                    'scope'       => 'Sample 5 items',
                    'methodology' => 'Inspect evidence, verify completeness',
                    'next_due'    => now()->addWeek(),
                ]
            );

            // Add some historical executions
            foreach ([2, 1] as $m) {
                ControlTestExecution::firstOrCreate(
                    [
                        'plan_id'    => $plan->id,
                        'executed_at'=> now()->subMonths($m)->startOfDay()->addHours(10),
                    ],
                    [
                        'executed_by'         => $tester?->id,
                        'result'              => $m % 2 === 0 ? 'pass' : 'partial',
                        'effectiveness_rating'=> $m % 2 === 0 ? 'Effective' : 'Partial',
                        'comments'            => 'Demo execution',
                    ]
                );
            }
        }
    }
}
