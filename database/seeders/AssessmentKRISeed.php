<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\AssessmentTemplate;
use App\Models\Kri;

class AssessmentKRISeed extends Seeder {
  public function run(): void {
    AssessmentTemplate::firstOrCreate(['title'=>'Standard Risk Questionnaire'],[
      'description'=>'Baseline risk assessment questionnaire',
      'entity_type'=>'risk',
      'schema_json'=>[
        'type'=>'object',
        'properties'=>[
          'context'=>['type'=>'string','title'=>'Context'],
          'existing_controls'=>['type'=>'string','title'=>'Existing Controls'],
          'likelihood'=>['type'=>'number','title'=>'Updated Likelihood (1-5)'],
          'impact'=>['type'=>'number','title'=>'Updated Impact (1-5)'],
        ],
        'required'=>['context']
      ],
      'ui_schema_json'=>[ 'likelihood'=>['ui:widget'=>'range'], 'impact'=>['ui:widget'=>'range'] ],
      'status'=>'active'
    ]);

    Kri::firstOrCreate(['title'=>'Open Security Incidents (Monthly)', 'entity_type'=>'org_unit', 'entity_id'=>1],[
      'unit'=>'count','cadence'=>'monthly','target'=>0,'warn_threshold'=>5,'alert_threshold'=>10,'direction'=>'lower_is_better'
    ]);
  }
}
