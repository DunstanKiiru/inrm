<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Risk;
use App\Models\RiskCategory;
use App\Models\OrgUnit;
use App\Models\RiskCause;
use App\Models\RiskConsequence;
use App\Models\User;

class DemoRisksSeed extends Seeder
{
    public function run(): void
    {
        // Ensure taxonomy exists (assumes RiskTaxonomySeed ran)
        $categoryMap = RiskCategory::pluck('id','name');
        $orgMap = OrgUnit::pluck('id','name');
        $causeIds = RiskCause::pluck('id')->all();
        $consIds  = RiskConsequence::pluck('id')->all();

        if ($categoryMap->isEmpty() || $orgMap->isEmpty()) {
            $this->command->warn('Run RiskTaxonomySeed first.');
            return;
        }

        $owner = User::first(); // fallback to first user
        $ownerId = $owner?->id;

        // Curated demo set (15 risks) to cover the heatmap evenly
        $demos = [
            ['title'=>'Email phishing campaign',            'cat'=>'Cyber',       'org'=>'IT',         'L'=>3,'I'=>3,'w'=>1.0,'status'=>'active'],
            ['title'=>'Ransomware exposure',                'cat'=>'Cyber',       'org'=>'IT',         'L'=>4,'I'=>5,'w'=>1.2,'status'=>'active'],
            ['title'=>'Vendor data leak',                   'cat'=>'Third-Party', 'org'=>'IT',         'L'=>3,'I'=>5,'w'=>1.1,'status'=>'active'],
            ['title'=>'Core system outage',                 'cat'=>'Operational', 'org'=>'Operations', 'L'=>2,'I'=>5,'w'=>1.0,'status'=>'active'],
            ['title'=>'Regulatory reporting delay',         'cat'=>'Compliance',  'org'=>'Finance',    'L'=>2,'I'=>3,'w'=>0.9,'status'=>'active'],
            ['title'=>'Fraudulent transactions',            'cat'=>'Financial',   'org'=>'Finance',    'L'=>2,'I'=>4,'w'=>1.0,'status'=>'active'],
            ['title'=>'Payment gateway instability',        'cat'=>'Operational', 'org'=>'IT',         'L'=>3,'I'=>4,'w'=>1.0,'status'=>'active'],
            ['title'=>'HR data privacy breach',             'cat'=>'Compliance',  'org'=>'Head Office','L'=>2,'I'=>5,'w'=>1.0,'status'=>'active'],
            ['title'=>'Supply chain disruption',            'cat'=>'Operational', 'org'=>'Operations', 'L'=>3,'I'=>3,'w'=>1.0,'status'=>'active'],
            ['title'=>'Critical patch backlog',             'cat'=>'Cyber',       'org'=>'IT',         'L'=>4,'I'=>3,'w'=>0.8,'status'=>'active'],
            ['title'=>'Revenue recognition error',          'cat'=>'Financial',   'org'=>'Finance',    'L'=>2,'I'=>4,'w'=>0.9,'status'=>'active'],
            ['title'=>'Physical security incident',         'cat'=>'Operational', 'org'=>'Head Office','L'=>1,'I'=>4,'w'=>1.0,'status'=>'active'],
            ['title'=>'Third‑party SLA breach',             'cat'=>'Third-Party', 'org'=>'Operations', 'L'=>3,'I'=>2,'w'=>1.0,'status'=>'active'],
            ['title'=>'Unapproved cloud usage (shadow IT)','cat'=>'Cyber',       'org'=>'IT',         'L'=>3,'I'=>2,'w'=>0.7,'status'=>'active'],
            ['title'=>'Model risk in credit scoring',       'cat'=>'Financial',   'org'=>'Finance',    'L'=>2,'I'=>5,'w'=>1.3,'status'=>'active'],
        ];

        foreach ($demos as $d) {
            $catId = $categoryMap[$d['cat']] ?? null;
            $orgId = $orgMap[$d['org']] ?? null;
            $inherent = $d['I'] * $d['L'] * $d['w'];

            $risk = Risk::create([
                'title'           => $d['title'],
                'description'     => 'Demo risk seeded for heatmap and rollups.',
                'owner_id'        => $ownerId,
                'category_id'     => $catId,
                'org_unit_id'     => $orgId,
                'likelihood'      => $d['L'],
                'impact'          => $d['I'],
                'weight'          => $d['w'],
                'inherent_score'  => $inherent,
                'residual_score'  => round($inherent * 0.6, 1),
                'status'          => $d['status'],
                'custom_json'     => ['source'=>'seed','ref'=>Str::uuid()->toString()],
            ]);

            // Randomly attach 1-2 causes & consequences (if relations exist)
            if (method_exists($risk, 'causes') && $causeIds) {
                $risk->causes()->sync(array_slice($causeIds, 0, min(2, count($causeIds))));
            } else {
                foreach (array_slice($causeIds, 0, min(2, count($causeIds))) as $cid) {
                    DB::table('cause_risk')->insert(['risk_id'=>$risk->id,'risk_cause_id'=>$cid]);
                }
            }
            if (method_exists($risk, 'consequences') && $consIds) {
                $risk->consequences()->sync(array_slice($consIds, 0, min(2, count($consIds))));
            } else {
                foreach (array_slice($consIds, 0, min(2, count($consIds))) as $cid) {
                    DB::table('consequence_risk')->insert(['risk_id'=>$risk->id,'risk_consequence_id'=>$cid]);
                }
            }
        }

        $this->command->info('Seeded demo risks (15).');
    }
}
