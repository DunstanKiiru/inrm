<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Obligation extends Model {
  use HasFactory;
  protected $fillable = [
    'title', 'jurisdiction', 'source_doc_url', 'summary', 'review_cycle'
  ];

  public function requirements() {
    return $this->belongsToMany(Requirement::class);
  }
}
