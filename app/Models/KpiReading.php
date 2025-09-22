<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KpiReading extends Model {
  use HasFactory;
  protected $fillable = ['kpi_id','ts','value'];
  protected $casts = ['ts'=>'datetime'];
  public function kpi(){ return $this->belongsTo(Kpi::class); }
}
