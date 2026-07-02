<?php

use App\Modules\Auth\Http\Controllers\Api\MemberAuthController;
use App\Modules\Auth\Http\Controllers\Api\MemberQuotaController;
use App\Modules\Auth\Http\Controllers\Api\MemberWebauthnController;
use App\Modules\Payments\Http\Controllers\Api\MemberPaymentController;
use App\Support\ModuleRegistry;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware('api')
    ->group(function (): void {
        Route::middleware('module:'.ModuleRegistry::AREA_SOCIO)->group(function (): void {
            Route::post('/login', [MemberAuthController::class, 'login'])
                ->middleware('throttle:api-login');

            Route::middleware('passkeys.enabled')->group(function (): void {
                Route::post('/webauthn/login/options', [MemberWebauthnController::class, 'loginOptions'])
                    ->middleware('throttle:webauthn-login');
                Route::post('/webauthn/login', [MemberWebauthnController::class, 'login'])
                    ->middleware('throttle:webauthn-login');
            });

            Route::middleware('auth:sanctum')->group(function (): void {
                Route::post('/logout', [MemberAuthController::class, 'logout']);
                Route::get('/me', [MemberAuthController::class, 'me']);
                Route::put('/me/password', [MemberAuthController::class, 'updatePassword']);

                Route::middleware('passkeys.enabled')->group(function (): void {
                    Route::get('/webauthn/keys', [MemberWebauthnController::class, 'index']);
                    Route::post('/webauthn/keys/options', [MemberWebauthnController::class, 'storeOptions']);
                    Route::post('/webauthn/keys', [MemberWebauthnController::class, 'store']);
                    Route::delete('/webauthn/keys/{key}', [MemberWebauthnController::class, 'destroy']);
                });

                Route::middleware('member.password.changed')->group(function (): void {
                    Route::get('/me/quota', [MemberQuotaController::class, 'show']);
                    Route::get('/me/payments', [MemberPaymentController::class, 'index']);
                    Route::get('/me/payments/{payment}/receipt', [MemberPaymentController::class, 'receipt'])
                        ->middleware('module:'.ModuleRegistry::COMPROVATIVOS);
                });
            });
        });
    });
