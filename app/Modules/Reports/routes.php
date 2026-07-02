<?php

use App\Modules\Reports\Http\Controllers\OverdueMembersReportController;
use App\Modules\Reports\Http\Controllers\PayingMembersReportController;
use App\Support\ModuleRegistry;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'staff', 'module:'.ModuleRegistry::RELATORIOS])->group(function (): void {
    Route::get('/relatorios/socios-em-atraso.pdf', [OverdueMembersReportController::class, 'pdf'])
        ->name('reports.overdue.pdf');
    Route::get('/relatorios/socios-em-atraso.csv', [OverdueMembersReportController::class, 'excel'])
        ->name('reports.overdue.excel');
    Route::get('/relatorios/socios-pagantes.pdf', [PayingMembersReportController::class, 'pdf'])
        ->name('reports.paying.pdf');
    Route::get('/relatorios/socios-pagantes.csv', [PayingMembersReportController::class, 'excel'])
        ->name('reports.paying.excel');
});
