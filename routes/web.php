<?php

use App\Http\Controllers\LaporanController;
use App\Http\Controllers\UsahaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login',  [UsahaController::class, 'showLogin'])->name('login');
    Route::post('login', [UsahaController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [UsahaController::class, 'logout'])->name('logout');
    Route::get('/',       [LaporanController::class, 'dashboard'])->name('dashboard');
});
