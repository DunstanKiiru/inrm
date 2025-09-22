<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Obligation extends Model {
  use HasFactory;
  protected $fillable = ['title','jurisdiction','source_doc_url','summary','effective_date','review_cycle','owner_id'];
  protected $casts = ['effective_date'=>'date'];
  public function requirements(){ return $this->belongsToMany(Requirement::class,'obligation_requirement','obligation_id','requirement_id'); }
  public function owner(){ return $this->belongsTo(User::class,'owner_id'); }
}
