<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiskCause extends Model {
  use HasFactory;
  protected $fillable = [
    'name'
  ];

  public function risks() {
    return $this->belongsToMany(Risk::class, 'cause_risk');
  }
}
