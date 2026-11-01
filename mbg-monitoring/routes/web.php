<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportReviewController;
use App\Http\Controllers\ReportForwardingController;

Route::get('/', function () {
    return view('welcome');
});

// Route untuk guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [
        AuthController::class, 'showLogin'
    ])->name('login');

    Route::post('/login', [
        AuthController::class, 'login'
    ])->middleware('throttle:10,1')
      ->name('login.store');
});

// Route yang membutuhkan login
Route::middleware('auth')->group(function () {
    Route::get(
        '/reports/{report}/evidence',
        [ReportController::class, 'downloadEvidence']
    )->name('reports.evidence');

    Route::resource('reports', ReportController::class)
        ->except(['destroy']);

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Area khusus Pelapor
    Route::get('/pelapor', function () {
        return view('role-area', [
            'title' => 'Area Pelapor'
        ]);
    })->middleware('role:pelapor')
      ->name('pelapor.home');

    // Area khusus Pemantau
    Route::get('/pemantau', function () {
        return view('role-area', [
            'title' => 'Area Pemantau Komunitas'
        ]);
    })->middleware('role:pemantau')
      ->name('pemantau.home');

    // Area khusus Koordinator
    Route::get('/koordinator', function () {
        return view('role-area', [
            'title' => 'Area Koordinator Komunitas'
        ]);
    })->middleware('role:koordinator')
      ->name('koordinator.home');

    Route::post('/logout', [
        AuthController::class, 'logout'
    ])->name('logout');

    
    // Proses peninjauan oleh Pemantau Komunitas
    Route::post('/reports/{report}/review/start', [
        ReportReviewController::class, 'start'
    ])->middleware('role:pemantau')
    ->name('reports.review.start');

    Route::post('/reports/{report}/reviews', [
        ReportReviewController::class, 'store'
    ])->middleware('role:pemantau')
    ->name('reports.reviews.store');

    // Proses penerusan oleh Koordinator Komunitas
    Route::post('/reports/{report}/forwardings', [
        ReportForwardingController::class, 'store'
    ])->middleware('role:koordinator')
    ->name('reports.forwardings.store');

    Route::post('/reports/{report}/close', [
        ReportForwardingController::class, 'close'
    ])->middleware('role:koordinator')
    ->name('reports.close');
    
});
