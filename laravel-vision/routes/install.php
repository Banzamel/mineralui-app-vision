<?php

use Illuminate\Support\Facades\Route;

// Installer — public before installation, blocked once installed.
Route::prefix('install')->group(function () {
    Route::get('/status', \App\Http\Controllers\Install\GetInstallStatusController::class)->name('install.status');

    Route::middleware('install.gate')->group(function () {
        Route::post('/test-database', \App\Http\Controllers\Install\TestDatabaseController::class)->name('install.database.test');
        Route::post('/database', \App\Http\Controllers\Install\SaveDatabaseController::class)->name('install.database.save');
        Route::post('/admin', \App\Http\Controllers\Install\CreateAdminController::class)->name('install.admin');
        Route::post('/first-object', \App\Http\Controllers\Install\CreateFirstObjectController::class)->name('install.first-object');
        Route::post('/first-camera', \App\Http\Controllers\Install\CreateFirstCameraController::class)->name('install.first-camera');
        Route::post('/finalize', \App\Http\Controllers\Install\FinalizeInstallController::class)->name('install.finalize');
    });
});
