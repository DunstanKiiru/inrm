<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kpi extends Model {
  use HasFactory;
  protected $fillable = ['key','title','unit','target','direction','owner_id'];
  // direction: up|down (which direction is "good")
  public function readings(){ return $this->hasMany(KpiReading::class); }
  public function owner(){ return $this->belongsTo(User::class,'owner_id'); }
}
