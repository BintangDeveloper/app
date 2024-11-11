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
  return JwtTokenHelper::generateToken([
    'permission' => 2
  ], 86400);
});
