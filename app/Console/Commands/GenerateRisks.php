<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Risk;
use App\Models\RiskCategory;
use App\Models\OrgUnit;
use App\Models\User;

class GenerateRisks extends Command
{
    protected $signature = 'risks:generate {count=100}';
    protected $description = 'Generate random risks for heatmap/testing';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $cats = RiskCategory::pluck('id')->all();
        $orgs = OrgUnit::pluck('id')->all();
        $owner = User::first();
        if (!$cats || !$orgs || !$owner) {
            $this->warn('Require categories, org units, and at least one user.');
            return 0;
        }

        $titles = ['Outage','Misconfiguration','Data loss','Breach','Fraud','Process gap','Vendor issue','Compliance lapse','Shadow IT','Insider threat'];

        for ($i=0;$i<$count;$i++) {
            $L = rand(1,5);
            $I = rand(1,5);
            $w = [0.6,0.8,1.0,1.2,1.4][array_rand([0,1,2,3,4])];
            $inherent = $L * $I * $w;

            Risk::create([
                'title'          => $titles[array_rand($titles)].' #'.Str::upper(Str::random(4)),
                'description'    => 'Auto-generated demo risk.',
                'owner_id'       => $owner->id,
                'category_id'    => $cats[array_rand($cats)],
                'org_unit_id'    => $orgs[array_rand($orgs)],
                'likelihood'     => $L,
                'impact'         => $I,
                'weight'         => $w,
                'inherent_score' => $inherent,
                'residual_score' => round($inherent * (0.4 + (rand(0,30)/100)), 1),
                'status'         => ['active','draft','closed'][array_rand([0,1,2])],
                'custom_json'    => ['source'=>'generator'],
            ]);
        }

        $this->info("Generated {$count} risks.");
        return 0;
    }
}
