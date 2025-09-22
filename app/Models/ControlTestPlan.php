<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ControlTestPlan extends Model {
  use HasFactory;
  protected $fillable = ['control_id','test_type','frequency','next_due','assigned_to','status','scope','methodology'];
  protected $casts = ['next_due'=>'datetime'];
  public function control(){ return $this->belongsTo(Control::class); }
  public function assignee(){ return $this->belongsTo(User::class,'assigned_to'); }
  public function executions(){ return $this->hasMany(ControlTestExecution::class,'plan_id'); }
}
