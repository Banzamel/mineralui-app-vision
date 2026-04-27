<?php

use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('oauth')->group(function () {
    Route::post('/login', \App\Http\Controllers\Auth\LoginController::class);
    Route::post('/refresh', \App\Http\Controllers\Auth\RefreshTokenController::class);

    // Social login (Google, Facebook)
    Route::get('/social/{provider}/redirect', \App\Http\Controllers\Auth\SocialRedirectController::class);
    Route::get('/social/{provider}/callback', \App\Http\Controllers\Auth\SocialCallbackController::class);

    Route::middleware(['auth:api', 'scope:api'])->group(function () {
        Route::post('/logout', \App\Http\Controllers\Auth\LogoutController::class);
    });
});
