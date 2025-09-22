<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BcmSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Business Processes
        $processes = [
            ['name' => 'Finance Operations', 'description' => 'Manage financial transactions and reporting.'],
            ['name' => 'IT Services', 'description' => 'Manage IT infrastructure and support.'],
            ['name' => 'HR Management', 'description' => 'Handle employee records and payroll.'],
        ];

        foreach ($processes as $process) {
            $processId = DB::table('business_processes')->insertGetId([
                'name' => $process['name'],
                'description' => $process['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Seed BCM Risks for this process
            $risks = [
                ['name' => 'Data Loss', 'description' => 'Critical data could be lost.', 'impact_score' => rand(1,5), 'likelihood_score' => rand(1,5)],
                ['name' => 'System Downtime', 'description' => 'IT systems may be unavailable.', 'impact_score' => rand(1,5), 'likelihood_score' => rand(1,5)],
            ];

            foreach ($risks as $risk) {
                $riskId = DB::table('bcm_risks')->insertGetId([
                    'business_process_id' => $processId,
                    'name' => $risk['name'],
                    'description' => $risk['description'],
                    'impact_score' => $risk['impact_score'],
                    'likelihood_score' => $risk['likelihood_score'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Seed BCM Controls for each risk
                $controls = [
                    ['name' => 'Backup Data', 'description' => 'Daily backup to cloud storage.'],
                    ['name' => 'Monitor Systems', 'description' => '24/7 monitoring of key systems.'],
                ];

                foreach ($controls as $control) {
                    DB::table('bcm_controls')->insert([
                        'bcm_risk_id' => $riskId,
                        'name' => $control['name'],
                        'description' => $control['description'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Seed Recovery Plans for each process
            $plans = [
                ['name' => 'Finance Recovery Plan', 'steps' => 'Restore backup, notify stakeholders, resume operations.'],
                ['name' => 'IT Recovery Plan', 'steps' => 'Switch to DR site, restore critical servers.'],
            ];

            foreach ($plans as $plan) {
                DB::table('recovery_plans')->insert([
                    'business_process_id' => $processId,
                    'name' => $plan['name'],
                    'steps' => $plan['steps'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
