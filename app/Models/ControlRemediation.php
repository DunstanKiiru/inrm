<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ControlRemediation extends Model {
  use HasFactory;
  protected $fillable = ['issue_id','description','assigned_to','due_date','status'];
  protected $casts = ['due_date'=>'datetime'];
  public function issue(){ return $this->belongsTo(ControlIssue::class,'issue_id'); }
  public function assignee(){ return $this->belongsTo(User::class,'assigned_to'); }
}
