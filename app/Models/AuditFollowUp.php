<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditFollowUp extends Model {
  use HasFactory;
  protected $fillable = ['finding_id','test_date','result','notes','tester_id','evidence_url'];
  protected $casts = ['test_date'=>'datetime'];
  public function finding(){ return $this->belongsTo(AuditFinding::class,'finding_id'); }
  public function tester(){ return $this->belongsTo(User::class,'tester_id'); }
}
