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
    
    Route::get(
      '/{BUCKET_ID}/list', 'listFiles'
    );
    
    Route::get(
      '/{BUCKET_ID}/info/{FILE_ID}', 'getFileInfo'
    );
    
    Route::get(
      '/{BUCKET_ID}/download/{FILE_ID}', 'getFileDownload'
    );
    
    Route::get(
      '/{BUCKET_ID}/view/{FILE_ID}', 'getFileView'
    );
    
    Route::get(
      '/{BUCKET_ID}/preview/{FILE_ID}', 'getFilePreview'
    );
    
    Route::post(
      '/{BUCKET_ID}/upload', 'uploadFile'
    );
});

Route::prefix('test')->group(function () {
  
  Route::get('/ping', function (Request $request) {
    return ResponseHelper::success("Hello World!");
  });
  
});