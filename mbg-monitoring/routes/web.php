<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Hanya pengguna yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [
        AuthController::class, 'showLogin'
    ])->name('login');

    Route::post('/login', [
        AuthController::class, 'login'
    ])->middleware('throttle:10,1')
      ->name('login.store');
});

// Hanya pengguna yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [
        AuthController::class, 'logout'
    ])->name('logout');
});
