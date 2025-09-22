<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Evidence extends Model {
  use HasFactory;
  protected $fillable=['entity_type','entity_id','filename','storage_disk','storage_path','size','sha256','mime','uploaded_by','scanned_status'];
}