<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/up', function (Request $request) {
  return response('Hello World', 200)->header('Content-Type', 'text/plain');
});