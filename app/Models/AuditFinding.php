<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditFinding extends Model {
  use HasFactory;
  protected $fillable = [
    'audit_plan_id','audit_procedure_id','title','description','severity','rating','cause','impact','criteria','condition','recommendation','owner_id','target_date','status','risk_id'
  ];
  protected $casts = ['target_date'=>'date'];
  public function plan(){ return $this->belongsTo(AuditPlan::class,'audit_plan_id'); }
  public function procedure(){ return $this->belongsTo(AuditProcedure::class,'audit_procedure_id'); }
  public function owner(){ return $this->belongsTo(User::class,'owner_id'); }
  public function followups(){ return $this->hasMany(AuditFollowUp::class,'finding_id'); }
}
