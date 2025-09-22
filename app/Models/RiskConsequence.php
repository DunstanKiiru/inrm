<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiskConsequence extends Model {
  use HasFactory;
  protected $fillable = [
    'name'
  ];

  public function risks() {
    return $this->belongsToMany(Risk::class, 'consequence_risk');
  }
}
