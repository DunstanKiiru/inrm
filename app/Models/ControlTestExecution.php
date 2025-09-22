<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ControlTestExecution extends Model {
  use HasFactory;
  protected $fillable = ['plan_id','executed_at','executed_by','result','comments','effectiveness_rating'];
  protected $casts = ['executed_at'=>'datetime'];
  public function plan(){ return $this->belongsTo(ControlTestPlan::class,'plan_id'); }
  public function executor(){ return $this->belongsTo(User::class,'executed_by'); }
}
