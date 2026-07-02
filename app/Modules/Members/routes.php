<?php

use App\Modules\Members\Http\Controllers\MemberCardController;
use App\Modules\Members\Http\Controllers\MemberImportController;
use App\Modules\Members\Http\Controllers\MemberValidationController;
use App\Support\ModuleRegistry;
use Illuminate\Support\Facades\Route;

Route::get('/validar/{member}', [MemberValidationController::class, 'show'])
    ->name('member.validate')
    ->middleware('signed');

Route::middleware(['auth', 'staff'])->group(function (): void {
    Route::get('/relatorios/socios/modelo-importacao.xlsx', [MemberImportController::class, 'template'])
        ->name('members.import.template');
    Route::get('/relatorios/socios/exportacao.xlsx', [MemberImportController::class, 'export'])
        ->name('members.export.excel');

    Route::middleware('module:'.ModuleRegistry::CARTOES)->group(function (): void {
        Route::get('/cartao/{member}', [MemberCardController::class, 'show'])->name('member.card');
        Route::get('/cartao/{member}/verso', [MemberCardController::class, 'showVerso'])->name('member.card.verso');
        Route::get('/cartao/{member}/pdf', [MemberCardController::class, 'pdf'])->name('member.card.pdf');
        Route::get('/cartao/{member}/png', [MemberCardController::class, 'png'])->name('member.card.png');
        Route::get('/cartao/{member}/png-verso', [MemberCardController::class, 'pngVerso'])->name('member.card.png.verso');
        Route::get('/relatorios/cartoes.zip', [MemberCardController::class, 'exportZip'])->name('reports.cards.zip');
    });
});
