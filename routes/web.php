<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;

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
  Route::get('/dashboard', [
    DashboardController::class, 'index'
  ]);
  
  Route::delete('/auth/logout', [
    AuthController::class, 'logout'
  ])->name('logout');
  
  Route::get('/', [
    HomeController::class, 'index'
  ]);
});
