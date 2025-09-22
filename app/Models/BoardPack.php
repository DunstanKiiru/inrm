<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoardPack extends Model {
  use HasFactory;
  protected $fillable = ['title','dashboard_id','from_date','to_date','filters_json','status'];
  protected $casts = ['from_date'=>'date','to_date'=>'date','filters_json'=>'array'];
  public function dashboard(){ return $this->belongsTo(Dashboard::class); }
}
