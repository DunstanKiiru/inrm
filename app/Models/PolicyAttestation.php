<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PolicyAttestation extends Model {
  use HasFactory;
  protected $fillable = ['policy_id','policy_version_id','user_id','attested_at'];
  protected $casts = ['attested_at'=>'datetime'];
  public function policy(){ return $this->belongsTo(Policy::class); }
  public function version(){ return $this->belongsTo(PolicyVersion::class,'policy_version_id'); }
  public function user(){ return $this->belongsTo(User::class); }
}
