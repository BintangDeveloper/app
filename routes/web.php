<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Helpers\Rsa\RSAKeyManager;
use App\Helpers\Aes\AESEncryptionHelper;

Route::get('/', function (Request $request) {
    return view('welcome');
//    return App\Helpers\ResponseHelper::error(
//       "You don't have permission to access that or it is not allowed to access by anyone!", [
//         $request
//       ], 503);
});

Route::get('/_/gen', function (Request $request) {
  $aes = new AESEncryptionHelper(env('RSA_PASSPHRASE', null));
  
  $rsa = new RSAKeyManager(
    base64_decode(env('RSA_PRIVATE_KEY')), 
    env('RSA_PASSPHRASE', null)
  );
  
  return base64_encode($aes->encrypt($rsa->generatePublicKey()));
});
