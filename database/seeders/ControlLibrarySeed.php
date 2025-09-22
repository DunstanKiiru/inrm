<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ControlCategory;
use App\Models\Control;

class ControlLibrarySeed extends Seeder
{
    public function run(): void
    {
        // --- Categories ------------------------------------------------------
        $cats = [
            'Information Security' => 'Policies, standards, access, monitoring',
            'Operational'          => 'Process controls, quality checks',
            'Financial'            => 'Accounting, approvals, reconciliations',
            'IT General Controls'  => 'Change, access, operations',
        ];

        $catIds = [];
        foreach ($cats as $name => $desc) {
            $catIds[$name] = ControlCategory::firstOrCreate(
                ['name' => $name],
                ['description' => $desc]
            )->id;
        }

        // --- Controls --------------------------------------------------------
        $controls = [
            [
                'title'    => 'Multi-factor Authentication',
                'category' => 'Information Security',
                'nature'   => 'preventive',
                'type'     => 'automated',
                'frequency'=> 'ad-hoc',
            ],
            [
                'title'    => 'Daily Log Review',
                'category' => 'Information Security',
                'nature'   => 'detective',
                'type'     => 'manual',
                'frequency'=> 'daily',
            ],
            [
                'title'    => 'Quarterly Access Recertification',
                'category' => 'Information Security',
                'nature'   => 'preventive',
                'type'     => 'manual',
                'frequency'=> 'quarterly',
            ],
            [
                'title'    => 'Change Management Approval',
                'category' => 'IT General Controls',
                'nature'   => 'preventive',
                'type'     => 'manual',
                'frequency'=> 'ad-hoc',
            ],
            [
                'title'    => 'Vendor Due Diligence Review',
                'category' => 'Operational',
                'nature'   => 'preventive',
                'type'     => 'manual',
                'frequency'=> 'annual',
            ],
            [
                'title'    => 'Bank Reconciliations',
                'category' => 'Financial',
                'nature'   => 'detective',
                'type'     => 'manual',
                'frequency'=> 'monthly',
            ],
        ];

        foreach ($controls as $c) {
            Control::firstOrCreate(
                ['title' => $c['title']],
                [
                    'description' => 'Seed control',
                    'category_id' => $catIds[$c['category']] ?? null,
                    'nature'      => $c['nature'],
                    'type'        => $c['type'],
                    'frequency'   => $c['frequency'],
                    'status'      => 'active',
                ]
            );
        }
    }
}
