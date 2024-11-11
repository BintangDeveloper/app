<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Helpers\JwtTokenHelper;

Route::get('/', function (Request $request) {
    return view('welcome');
//    return App\Helpers\ResponseHelper::error(
//       "You don't have permission to access that or it is not allowed to access by anyone!", [
//         $request
//       ], 503);
});

Route::get('/_/gen', function (Request $request) {
  JwtHelper::initialize(env('JWT_KEY', 'nokey'));
  
  return JwtHelper::createToken([
    'sub' => 'BintangDeveloperServers', 
    'permission' => 3,
    
    'iss' => 'https://www.bintangdeveloper.eu.org',
    'aud' => 'https://www.bintangdeveloper.eu.org',
    
    'security' => base64_encode(random_bytes(16))
  ]);
});
