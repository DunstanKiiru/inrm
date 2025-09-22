<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssessmentRound extends Model {
  use HasFactory;
  protected $fillable = ['assessment_id','round_no','due_at','status','assigned_to'];
  protected $casts = ['due_at'=>'datetime'];
  public function assessment(){ return $this->belongsTo(Assessment::class,'assessment_id'); }
  public function responses(){ return $this->hasMany(AssessmentResponse::class,'round_id'); }
  public function assignee(){ return $this->belongsTo(User::class,'assigned_to'); }
}
