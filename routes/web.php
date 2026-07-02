<?php

use App\Http\Controllers\ClubBrandingLogoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::redirect('/login', '/admin/login')->name('login');

Route::get('/sw.js', function () {
    abort_unless(file_exists(public_path('sw.js')), 404);

    return response()->file(public_path('sw.js'), [
        'Content-Type' => 'application/javascript; charset=UTF-8',
        'Service-Worker-Allowed' => '/',
    ]);
})->name('pwa.sw');

Route::get('/branding/logo', ClubBrandingLogoController::class)->name('club.branding.logo');
