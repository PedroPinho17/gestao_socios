<?php

use App\Modules\Payments\Http\Controllers\PaymentReceiptController;
use App\Support\ModuleRegistry;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'staff'])->group(function (): void {
    Route::middleware('module:'.ModuleRegistry::COMPROVATIVOS)->group(function (): void {
        Route::get('/pagamentos/{payment}/comprovativo.pdf', [PaymentReceiptController::class, 'pdf'])
            ->name('payments.receipt.pdf');
    });
});
