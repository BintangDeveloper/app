<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;



Route::get('/welcome', function () {
    return view('welcome');
});

Route::group(['middleware' => 'guest'], function () {
  Route::get('/auth/register', [AuthController::class, 'register'])->name('register');
  Route::post('/auth/register', [AuthController::class, 'registerPost'])->name('register');
  Route::get('/auth/login', [AuthController::class, 'login'])->name('login');
  Route::post('/auth/login', [AuthController::class, 'loginPost'])->name('login');
});
Route::group(['middleware' => 'auth'], function () {
  Route::get('/home', [HomeController::class, 'index']);
  Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');
  
  Route::get('/', function () {
    return view('dashboard.index');
  });
});
