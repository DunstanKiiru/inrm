<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiskThreshold extends Model {
  use HasFactory;
  protected $fillable = [
    'profile_id', 'category_id', 'metric', 'operator', 'limit', 'band', 'color'
  ];

  public function profile() {
    return $this->belongsTo(RiskAppetiteProfile::class, 'profile_id');
  }

  public function category() {
    return $this->belongsTo(RiskCategory::class, 'category_id');
  }
}
