<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrgUnit extends Model {
  use HasFactory;
  protected $fillable = [
    'name', 'parent_id'
  ];

  public function parent() {
    return $this->belongsTo(OrgUnit::class, 'parent_id');
  }

  public function children() {
    return $this->hasMany(OrgUnit::class, 'parent_id');
  }

  public function risks() {
    return $this->hasMany(Risk::class, 'org_unit_id');
  }
}
