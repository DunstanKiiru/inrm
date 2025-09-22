<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessment extends Model {
  use HasFactory;
  protected $fillable = ['template_id','entity_type','entity_id','title','status'];
  public function template(){ return $this->belongsTo(AssessmentTemplate::class,'template_id'); }
  public function rounds(){ return $this->hasMany(AssessmentRound::class,'assessment_id'); }
}
