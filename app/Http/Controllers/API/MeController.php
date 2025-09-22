<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class MeController extends Controller {
  public function me(Request $r){
    return $r->user(); // returns id, name, email, etc.
  }
}