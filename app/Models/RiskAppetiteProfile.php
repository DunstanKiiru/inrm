<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiskAppetiteProfile extends Model {
  use HasFactory;
  protected $fillable = [
    'name', 'description'
  ];

  public function thresholds() {
    return $this->hasMany(RiskThreshold::class, 'profile_id');
  }
}
