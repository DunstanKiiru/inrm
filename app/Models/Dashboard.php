<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dashboard extends Model {
  use HasFactory;
  protected $fillable = ['slug','title','role','layout_json','is_default'];
  protected $casts = ['layout_json'=>'array','is_default'=>'boolean'];
  public function widgets(){ return $this->hasMany(ReportWidget::class); }
}
