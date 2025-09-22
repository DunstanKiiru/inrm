<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssessmentResponse extends Model {
  use HasFactory;
  protected $fillable = ['round_id','submitted_by','answers_json','status'];
  protected $casts = ['answers_json'=>'array'];
  public function round(){ return $this->belongsTo(AssessmentRound::class,'round_id'); }
  public function submitter(){ return $this->belongsTo(User::class,'submitted_by'); }
}
