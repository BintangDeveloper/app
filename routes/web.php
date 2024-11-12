<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Helpers\JwtHelper;
use App\Helpers\AESEncryptionHelper;

Route::get('/', function (Request $request) {
    return view('welcome');
//    return App\Helpers\ResponseHelper::error(
//       "You don't have permission to access that or it is not allowed to access by anyone!", [
//         $request
//       ], 503);
});

Route::get('/_/gen', function (Request $request) {
  JwtHelper::initialize(env('JWT_KEY', 'nokey'));
  
  $security = new AESEncryptionHelper('WTF');
  
  return JwtHelper::createToken([
    'sub' => hash('sha1', env('APP_NAME', 'APP')), 
    'permission' => 2,
    
    'iss' => 'https://www.bintangdeveloper.eu.org',
    'aud' => 'https://www.bintangdeveloper.eu.org',
    
    'security' => strtoupper(bin2hex($security->encrypt(json_encode([
      'app-id' => 0000000000,
      'app-secret' => 'e7dc0d49636f4194266ce34b5322a2861b7fb1bf',
      'app-permission' => 2
     ]))))
  ]);
});
