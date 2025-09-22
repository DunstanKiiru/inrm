<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditSample extends Model {
  use HasFactory;
  protected $fillable = [
    'audit_procedure_id','sample_no','population_ref','attributes_json','tested_at','result','notes'
  ];
  protected $casts = ['attributes_json'=>'array','tested_at'=>'datetime'];
  public function procedure(){ return $this->belongsTo(AuditProcedure::class,'audit_procedure_id'); }
}
