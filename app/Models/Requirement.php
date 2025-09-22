<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Requirement extends Model {
  use HasFactory;
  protected $fillable = ['framework_id','parent_id','code','title','description'];
  public function framework(){ return $this->belongsTo(Framework::class); }
  public function parent(){ return $this->belongsTo(Requirement::class,'parent_id'); }
  public function children(){ return $this->hasMany(Requirement::class,'parent_id'); }
  public function controls(){ return $this->belongsToMany(\App\Models\Control::class,'control_requirement','requirement_id','control_id'); }
}
