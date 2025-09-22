<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KriReading extends Model {
  use HasFactory;
  protected $fillable = ['kri_id','value','collected_at','source'];
  protected $casts = ['collected_at'=>'datetime','value'=>'float'];
  public function kri(){ return $this->belongsTo(Kri::class,'kri_id'); }
}
