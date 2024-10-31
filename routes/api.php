<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\ResponseHelper;

Route::prefix('auth')->group(function () {
  
  Route::get('/user', function (Request $request) {
    return $request->user();
  })->middleware('auth:sanctum');
  
});

Route::prefix('database')->group(function () {
  
});

Route::prefix('storage')->group(function () {
  
}); 

Route::prefix('test')->group(function () {
  
  Route::get('/ping', function (Request $request) {
    return ResponseHelper::success("Hello World!");
  });
  
});