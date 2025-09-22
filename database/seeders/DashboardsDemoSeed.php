<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kpi;
use App\Models\KpiReading;
use App\Models\Dashboard;
use App\Models\ReportWidget;

class DashboardsDemoSeed extends Seeder
{
    public function run(): void
    {
        // --- Seed KPIs ---
        $kpis = [
            ['key'=>'incidents_open','title'=>'Open Incidents','unit'=>'count','target'=>5,'direction'=>'down'],
            ['key'=>'controls_pass_rate','title'=>'Control Pass Rate','unit'=>'%','target'=>90,'direction'=>'up'],
            ['key'=>'vendor_renewals_30d','title'=>'Vendor Renewals (30d)','unit'=>'count','target'=>0,'direction'=>'down'],
        ];

        foreach ($kpis as $k) {
            $kpi = Kpi::firstOrCreate(
                ['key' => $k['key']],
                $k
            );

            // Seed 12 weeks of data
            for ($i = 11; $i >= 0; $i--) {
                $ts = now()->subWeeks($i)->startOfWeek();
                $val = match ($k['key']) {
                    'incidents_open' => max(0, 12 - $i + rand(-2,2)),
                    'controls_pass_rate' => min(100, 80 + $i + rand(-3,3)),
                    'vendor_renewals_30d' => rand(0,5),
                    default => rand(1,100),
                };
                KpiReading::firstOrCreate(
                    ['kpi_id' => $kpi->id, 'ts' => $ts],
                    ['value' => $val]
                );
            }
        }

        // --- Seed Dashboards ---
        $exec  = Dashboard::firstOrCreate(
            ['slug' => 'exec'],
            ['title' => 'Executive Scorecard', 'role' => 'Executive', 'is_default' => true]
        );
        $risk  = Dashboard::firstOrCreate(
            ['slug' => 'risk'],
            ['title' => 'Risk & Compliance', 'role' => 'Risk']
        );
        $audit = Dashboard::firstOrCreate(
            ['slug' => 'audit'],
            ['title' => 'Audit Overview', 'role' => 'Audit']
        );

        // --- Seed Report Widgets for Executive Dashboard ---
        ReportWidget::firstOrCreate(
            ['dashboard_id' => $exec->id, 'title' => 'Incidents — Open'],
            ['type' => 'kpi', 'order_index' => 1, 'config_json' => json_encode(['kpi_key' => 'incidents_open'])]
        );

        ReportWidget::updateOrCreate(
            ['dashboard_id' => $exec->id, 'title' => 'Open Incidents'],
            ['type' => 'kpi', 'order_index' => 1, 'config_json' => json_encode(['kpi_key' => 'incidents_open'])]
        );

        ReportWidget::updateOrCreate(
            ['dashboard_id' => $exec->id, 'title' => 'Control Effectiveness'],
            ['type' => 'kpi', 'order_index' => 2, 'config_json' => json_encode(['kpi_key' => 'controls_pass_rate'])]
        );

        ReportWidget::updateOrCreate(
            ['dashboard_id' => $exec->id, 'title' => 'Renewals 30d'],
            ['type' => 'kpi', 'order_index' => 3, 'config_json' => json_encode(['kpi_key' => 'vendor_renewals_30d'])]
        );

        ReportWidget::updateOrCreate(
            ['dashboard_id' => $exec->id, 'title' => 'Key Trends'],
            ['type' => 'chart', 'order_index' => 4, 'config_json' => json_encode(['kpi_keys' => ['incidents_open', 'controls_pass_rate']])]
        );

        // --- Seed Report Widgets for Risk Dashboard ---
        ReportWidget::updateOrCreate(
            ['dashboard_id' => $risk->id, 'title' => 'Risk Trends'],
            ['type' => 'chart', 'order_index' => 1, 'config_json' => json_encode(['kpi_keys' => ['controls_pass_rate','incidents_open']])]
        );

        // --- Seed Report Widgets for Audit Dashboard ---
        ReportWidget::updateOrCreate(
            ['dashboard_id' => $audit->id, 'title' => 'Audit KPIs'],
            ['type' => 'chart', 'order_index' => 1, 'config_json' => json_encode(['kpi_keys' => ['controls_pass_rate']])]
        );
    }
}
