<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kri extends Model {
  use HasFactory;
  protected $fillable = ['title','description','entity_type','entity_id','unit','cadence','target','warn_threshold','alert_threshold','direction'];
  // direction: 'higher_is_better' or 'lower_is_better'
  public function readings(){ return $this->hasMany(KriReading::class,'kri_id'); }
  public function breaches(){ return $this->hasMany(KriBreach::class,'kri_id'); }
}
