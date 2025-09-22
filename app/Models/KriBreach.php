<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KriBreach extends Model {
  use HasFactory;
  protected $fillable = ['kri_id','reading_id','level','message','acknowledged_at'];
  protected $casts = ['acknowledged_at'=>'datetime'];
  public function kri(){ return $this->belongsTo(Kri::class,'kri_id'); }
  public function reading(){ return $this->belongsTo(KriReading::class,'reading_id'); }
}
