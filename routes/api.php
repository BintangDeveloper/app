<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\ResponseHelper;

use App\Http\Controllers\StorageController;

Route::prefix('auth')->group(function () {
  
  Route::get('/user', function (Request $request) {
    return $request->user();
  })->middleware('auth:sanctum');
  
});

Route::prefix('database')->group(function () {

});

Route::prefix('storage')
  ->controller(StorageController::class)
  ->group(function () {
    
    Route::get('/{BUCKET_ID}/get/{FILE_ID}', 'get');
    Route::post('/{BUCKET_ID}/upload', 'upload');
});

Route::prefix('test')->group(function () {
  
  Route::get('/ping', function (Request $request) {
    return ResponseHelper::success("Hello World!");
  });
  
});