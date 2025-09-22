<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller; use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash; use Illuminate\Support\Str; use App\Models\User;
class SSOController extends Controller {
  public function redirectToAzure(){ return Socialite::driver('azure')->redirect(); }
  public function handleAzureCallback(){
    $su=Socialite::driver('azure')->stateless()->user(); $email=$su->getEmail(); $name=$su->getName() ?: 'User';
    $user=User::firstOrCreate(['email'=>$email],['name'=>$name,'password'=>Hash::make(Str::random(40))]);
    if(method_exists($user,'assignRole')){ try{$user->assignRole('Viewer');}catch(\Throwable $e){} }
    $token=$user->createToken('spa')->plainTextToken; return redirect(config('app.frontend_url','/').'?sso_token='.$token);
  }
}