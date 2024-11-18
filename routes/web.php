<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    return view('welcome');
//    return App\Helpers\ResponseHelper::error(
//       "You don't have permission to access that or it is not allowed to access by anyone!", [
//         $request
//       ], 503);
});

