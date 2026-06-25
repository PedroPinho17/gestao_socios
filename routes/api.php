<?php

use App\Http\Controllers\Api\ClubBrandingController;
use App\Http\Controllers\Api\MemberAuthController;
use App\Http\Controllers\Api\MemberPaymentController;
use App\Http\Controllers\Api\MemberQuotaController;
use Illuminate\Support\Facades\Route;

Route::get('/branding', [ClubBrandingController::class, 'show']);

Route::post('/login', [MemberAuthController::class, 'login'])
    ->middleware('throttle:api-login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [MemberAuthController::class, 'logout']);
    Route::get('/me', [MemberAuthController::class, 'me']);
    Route::put('/me/password', [MemberAuthController::class, 'updatePassword']);

    Route::middleware('member.password.changed')->group(function () {
        Route::get('/me/quota', [MemberQuotaController::class, 'show']);
        Route::get('/me/payments', [MemberPaymentController::class, 'index']);
    });
});
