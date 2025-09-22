<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditPlan extends Model {
  use HasFactory;
  protected $fillable = [
    'ref','title','scope','period_start','period_end','status','lead_id','team_json','objectives','methodology'
  ];
  protected $casts = ['period_start'=>'date','period_end'=>'date','team_json'=>'array'];
  public function lead(){ return $this->belongsTo(User::class,'lead_id'); }
  public function procedures(){ return $this->hasMany(AuditProcedure::class); }
  public function findings(){ return $this->hasMany(AuditFinding::class); }
}
