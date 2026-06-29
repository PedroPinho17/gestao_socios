<?php

namespace App\Services;

use App\Models\ClubSetting;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Http\Response;

class PaymentReceiptRenderer
{
    public function download(Payment $payment): Response
    {
        return $this->pdf($payment)->download($this->filename($payment));
    }

    public function pdfOutput(Payment $payment): string
    {
        return $this->pdf($payment)->output();
    }

    public function filename(Payment $payment): string
    {
        return 'comprovativo_'.$this->receiptNumber($payment).'.pdf';
    }

    public function receiptNumber(Payment $payment): string
    {
        $year = $payment->data?->format('Y') ?? now()->format('Y');

        return $year.'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
    }

    private function pdf(Payment $payment): DomPDF
    {
        $payment->loadMissing('member.quotaPlan');

        return Pdf::loadView('reports.payment-receipt-pdf', [
            'payment' => $payment,
            'member' => $payment->member,
            'settings' => ClubSetting::current(),
            'numeroRecibo' => $this->receiptNumber($payment),
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');
    }
}
