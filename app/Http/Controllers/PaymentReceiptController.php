<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentReceiptRenderer;
use Illuminate\Http\Response;

class PaymentReceiptController extends Controller
{
    public function pdf(Payment $payment, PaymentReceiptRenderer $renderer): Response
    {
        return $renderer->download($payment);
    }
}
