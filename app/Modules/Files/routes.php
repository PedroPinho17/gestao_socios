<?php

use App\Modules\Files\Http\Controllers\SecureFileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'staff'])->group(function (): void {
    Route::get('/files/members/{member}/photo', [SecureFileController::class, 'memberPhoto'])
        ->name('secure.member.photo');
    Route::get('/files/club/logo', [SecureFileController::class, 'clubLogo'])
        ->name('secure.club.logo');
});
