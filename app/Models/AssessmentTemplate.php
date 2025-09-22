<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssessmentTemplate extends Model {
  use HasFactory;
  protected $fillable = ['title','description','entity_type','schema_json','ui_schema_json','status'];
  protected $casts = ['schema_json'=>'array','ui_schema_json'=>'array'];
  public function assessments(){ return $this->hasMany(Assessment::class,'template_id'); }
}
