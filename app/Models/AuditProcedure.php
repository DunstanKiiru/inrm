<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditProcedure extends Model {
  use HasFactory;
  protected $fillable = [
    'audit_plan_id','ref','title','description','status','tester_id','population_size','sample_method','sample_size','results_summary'
  ];
  public function plan(){ return $this->belongsTo(AuditPlan::class,'audit_plan_id'); }
  public function tester(){ return $this->belongsTo(User::class,'tester_id'); }
  public function samples(){ return $this->hasMany(AuditSample::class); }
}
