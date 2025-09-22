<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ControlCategory extends Model {
  use HasFactory;
  protected $fillable = ['name','description'];
  public function controls(){ return $this->hasMany(Control::class,'category_id'); }
}
