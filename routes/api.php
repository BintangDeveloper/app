<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Helpers\Rsa\RSAKeyManager;
use App\Helpers\Aes\AESEncryptionHelper;

use App\Helpers\Response\JsonResponseHelper;
use App\Http\Controllers\StorageController;
use App\Http\Middleware\ApiAuthMiddleware;

use Illuminate\Support\Facades\Http;

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

Route::prefix('_')->group(function () {
  Route::get('/api-token', function (Request $request) {
      $aes = new AESEncryptionHelper(env('RSA_PASSPHRASE', null));
  
      $rsa = new RSAKeyManager(
        base64_decode(env('RSA_PRIVATE_KEY')), 
        env('RSA_PASSPHRASE', null)
      );
  
      return JsonResponseHelper::success(base64_encode($aes->encrypt($rsa->generatePublicKey())));
  });
  
  Route::get('/csrf-token', function(Request $request) {
    
      $token = session()->token(); // Alternatively: csrf_token()
      if (!$token) {
        session()->start(); // Start the session if not started
        $token = csrf_token(); // Re-generate the token
      }
      return JsonResponseHelper::success($token);
  });
});