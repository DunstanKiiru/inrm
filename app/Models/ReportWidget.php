<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportWidget extends Model {
  use HasFactory;
  protected $fillable = ['dashboard_id','type','title','config_json','order_index'];
  protected $casts = ['config_json'=>'array'];
  public function dashboard(){ return $this->belongsTo(Dashboard::class); }
}
