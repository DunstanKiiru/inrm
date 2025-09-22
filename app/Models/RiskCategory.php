<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiskCategory extends Model {
  use HasFactory;
  protected $fillable = [
    'name', 'parent_id'
  ];

  public function parent() {
    return $this->belongsTo(RiskCategory::class, 'parent_id');
  }

  public function children() {
    return $this->hasMany(RiskCategory::class, 'parent_id');
  }

  public function risks() {
    return $this->hasMany(Risk::class, 'category_id');
  }
}
