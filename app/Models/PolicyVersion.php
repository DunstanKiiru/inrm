<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PolicyVersion extends Model {
  use HasFactory;
  protected $fillable = [
    'policy_id', 'version', 'body_html', 'notes'
  ];

  public function policy() {
    return $this->belongsTo(Policy::class);
  }
}
