<?php

use App\Http\Controllers\Api\ClubBrandingController;
use Illuminate\Support\Facades\Route;

Route::get('/branding', [ClubBrandingController::class, 'show']);
