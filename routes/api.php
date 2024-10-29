<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\ResponseHelper;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('database')->group(function () {
  
});

Route::prefix('test')->group(function () {
  Route::get('/ping', function (Request $request) {
    return ResponseHelper::success("Hello World!");
  });
  
  Route::get('/appwrite', function (Request $request) {
    return ResponseHelper::error(
      json_encode(App\AppwriteClient::getClient()), [], 503
    );
  });
});