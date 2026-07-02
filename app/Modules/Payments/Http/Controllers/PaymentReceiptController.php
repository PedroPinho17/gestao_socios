<?php

namespace App\Modules\Payments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Services\PaymentReceiptRenderer;
use Illuminate\Http\Response;

class PaymentReceiptController extends Controller
{
    public function pdf(Payment $payment, PaymentReceiptRenderer $renderer): Response
    {
        return $renderer->download($payment);
    }
}
