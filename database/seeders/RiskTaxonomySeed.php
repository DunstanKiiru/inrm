<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\RiskCategory;
use App\Models\RiskCause;
use App\Models\RiskConsequence;
use App\Models\OrgUnit;

class RiskTaxonomySeed extends Seeder {
  public function run(): void {
    // Categories
    $ops = RiskCategory::firstOrCreate(['name'=>'Operational','parent_id'=>null]);
    $cyb = RiskCategory::firstOrCreate(['name'=>'Cyber','parent_id'=>null]);
    $fin = RiskCategory::firstOrCreate(['name'=>'Financial','parent_id'=>null]);
    $cmp = RiskCategory::firstOrCreate(['name'=>'Compliance','parent_id'=>null]);
    RiskCategory::firstOrCreate(['name'=>'Third-Party','parent_id'=>$ops->id]);

    // Causes
    foreach(['Human error','Process gap','System outage','Third-party failure','Fraud'] as $c){
      RiskCause::firstOrCreate(['name'=>$c]);
    }
    // Consequences
    foreach(['Financial loss','Regulatory penalty','Reputation damage','Service downtime','Safety incident'] as $c){
      RiskConsequence::firstOrCreate(['name'=>$c]);
    }
    // Org units
    $hq = OrgUnit::firstOrCreate(['name'=>'Head Office','parent_id'=>null]);
    OrgUnit::firstOrCreate(['name'=>'IT','parent_id'=>$hq->id]);
    OrgUnit::firstOrCreate(['name'=>'Operations','parent_id'=>$hq->id]);
    OrgUnit::firstOrCreate(['name'=>'Finance','parent_id'=>$hq->id]);
  }
}
