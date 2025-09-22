<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Framework;
use App\Models\Requirement;
use App\Models\Obligation;
use App\Models\Policy;
use App\Models\PolicyVersion;

class ComplianceSeed extends Seeder {
  public function run(): void {
    // ISO 27001 (subset)
    $iso = Framework::firstOrCreate(['key'=>'ISO27001'],['title'=>'ISO/IEC 27001','description'=>'Information security management systems','version'=>'2022']);
    $annexA = Requirement::firstOrCreate(['framework_id'=>$iso->id,'code'=>'Annex A','title'=>'Annex A Controls'],['description'=>'Annex A control clauses']);
    $a5 = Requirement::firstOrCreate(['framework_id'=>$iso->id,'parent_id'=>$annexA->id,'code'=>'A.5','title'=>'Information security policies']);
    Requirement::firstOrCreate(['framework_id'=>$iso->id,'parent_id'=>$a5->id,'code'=>'A.5.1','title'=>'Policies for information security']);
    $a8 = Requirement::firstOrCreate(['framework_id'=>$iso->id,'parent_id'=>$annexA->id,'code'=>'A.8','title'=>'Asset management']);
    Requirement::firstOrCreate(['framework_id'=>$iso->id,'parent_id'=>$a8->id,'code'=>'A.8.1','title'=>'Responsibility for assets']);
    $a12 = Requirement::firstOrCreate(['framework_id'=>$iso->id,'parent_id'=>$annexA->id,'code'=>'A.12','title'=>'Operations security']);
    Requirement::firstOrCreate(['framework_id'=>$iso->id,'parent_id'=>$a12->id,'code'=>'A.12.1','title'=>'Operational procedures and responsibilities']);

    // GDPR obligation (sample)
    $gdpr = Obligation::firstOrCreate(['title'=>'GDPR Article 5(1)(f): Integrity and confidentiality'],[
      'jurisdiction'=>'EU','source_doc_url'=>'https://gdpr.eu/article-5/','summary'=>'Personal data shall be processed in a manner that ensures appropriate security...','review_cycle'=>'annual'
    ]);
    // Map GDPR clause to ISO A.12.1 if exists
    $req = Requirement::where('framework_id',$iso->id)->where('code','A.12.1')->first();
    if($req){ $gdpr->requirements()->syncWithoutDetaching([$req->id]); }

    // Policy + version (published)
    $pol = Policy::firstOrCreate(['title'=>'Data Protection Policy'],[
      'status'=>'publish','effective_date'=>now()->subDays(1),'require_attestation'=>true
    ]);
    PolicyVersion::firstOrCreate(['policy_id'=>$pol->id,'version'=>1],[
      'body_html'=>'<h2>Data Protection Policy</h2><p>This policy defines requirements for protecting personal data.</p>',
      'notes'=>'Initial publication'
    ]);
  }
}
