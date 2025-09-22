<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\RiskAppetiteProfile;
use App\Models\RiskThreshold;
use App\Models\RiskCategory;

class RiskAppetiteSeed extends Seeder {
  public function run(): void {
    $profile = RiskAppetiteProfile::firstOrCreate(['name'=>'Default'],['description'=>'Baseline appetite profile']);
    $cyber = RiskCategory::where('name','Cyber')->first();
    // Example thresholds
    $defs = [
      ['category_id'=>null,'metric'=>'inherent','operator'=>'>=','limit'=>13,'band'=>'High','color'=>'#f4a261'],
      ['category_id'=>null,'metric'=>'inherent','operator'=>'>=','limit'=>21,'band'=>'Extreme','color'=>'#e76f51'],
      ['category_id'=>$cyber?->id,'metric'=>'residual','operator'=>'>=','limit'=>10,'band'=>'High','color'=>'#f94144'],
    ];
    foreach($defs as $d){
      RiskThreshold::firstOrCreate([
        'profile_id'=>$profile->id,'category_id'=>$d['category_id'],'metric'=>$d['metric'],'operator'=>$d['operator'],'limit'=>$d['limit'],'band'=>$d['band']
      ],['color'=>$d['color'] ?? null]);
    }
  }
}
