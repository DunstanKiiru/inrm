<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Policy extends Model {
  use HasFactory;
  protected $fillable = [
    'title', 'status', 'effective_date', 'require_attestation'
  ];
  protected $casts = [
    'effective_date' => 'date',
    'require_attestation' => 'boolean'
  ];

  public function versions() {
    return $this->hasMany(PolicyVersion::class);
  }
}
