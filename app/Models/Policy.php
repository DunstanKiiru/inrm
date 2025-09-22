<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Policy extends Model {
  use HasFactory;
  protected $fillable = ['title','status','owner_id','effective_date','review_date','require_attestation'];
  protected $casts = ['effective_date'=>'date','review_date'=>'date','require_attestation'=>'boolean'];
  public function owner(){ return $this->belongsTo(User::class,'owner_id'); }
  public function versions(){ return $this->hasMany(PolicyVersion::class); }
  public function latestVersion(){ return $this->hasOne(PolicyVersion::class)->latestOfMany('version'); }
  public function attestations(){ return $this->hasMany(PolicyAttestation::class); }
}
