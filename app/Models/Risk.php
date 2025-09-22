<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Risk extends Model {
  use HasFactory;
  protected $fillable = [
    'title', 'description', 'owner_id', 'category_id', 'org_unit_id',
    'likelihood', 'impact', 'weight', 'inherent_score', 'residual_score',
    'status', 'custom_json'
  ];
  protected $casts = [
    'custom_json' => 'array'
  ];

  public function owner() {
    return $this->belongsTo(User::class, 'owner_id');
  }

  public function category() {
    return $this->belongsTo(RiskCategory::class, 'category_id');
  }

  public function orgUnit() {
    return $this->belongsTo(OrgUnit::class, 'org_unit_id');
  }

  public function causes() {
    return $this->belongsToMany(RiskCause::class, 'cause_risk');
  }

  public function consequences() {
    return $this->belongsToMany(RiskConsequence::class, 'consequence_risk');
  }
}
