<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\Response\JsonResponseHelper;
use App\Http\Controllers\StorageController;
use App\Http\Middleware\ApiAuthMiddleware;

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
      '/list', 'listBucket'
    )->middleware(ApiAuthMiddleware::class);
    
    Route::get(
      '/{BUCKET_ID}/list', 'listFiles'
    )->middleware(ApiAuthMiddleware::class);
    
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
    return JsonResponseHelper::success("Hello World!");
  });
  
});