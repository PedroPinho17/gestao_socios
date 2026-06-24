<?php

use App\Http\Controllers\MemberValidationController;
use App\Http\Controllers\MemberCardController;
use App\Http\Controllers\OverdueMembersReportController;
use App\Http\Controllers\SecureFileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/validar/{member}', [MemberValidationController::class, 'show'])
    ->name('member.validate')
    ->middleware('signed');

Route::middleware(['auth'])->group(function () {
    Route::get('/cartao/{member}', [MemberCardController::class, 'show'])->name('member.card');
    Route::get('/cartao/{member}/verso', [MemberCardController::class, 'showVerso'])->name('member.card.verso');
    Route::get('/cartao/{member}/pdf', [MemberCardController::class, 'pdf'])->name('member.card.pdf');
    Route::get('/cartao/{member}/png', [MemberCardController::class, 'png'])->name('member.card.png');
    Route::get('/cartao/{member}/png-verso', [MemberCardController::class, 'pngVerso'])->name('member.card.png.verso');
    Route::get('/relatorios/cartoes.zip', [MemberCardController::class, 'exportZip'])->name('reports.cards.zip');
    Route::get('/relatorios/socios-em-atraso.pdf', [OverdueMembersReportController::class, 'pdf'])->name('reports.overdue.pdf');
    Route::get('/relatorios/socios-em-atraso.csv', [OverdueMembersReportController::class, 'excel'])->name('reports.overdue.excel');
    Route::get('/files/members/{member}/photo', [SecureFileController::class, 'memberPhoto'])->name('secure.member.photo');
    Route::get('/files/club/logo', [SecureFileController::class, 'clubLogo'])->name('secure.club.logo');
});
