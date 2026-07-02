<?php

namespace App\Modules\Payments\Mail;

use App\Models\ClubSetting;
use App\Modules\Payments\Models\Payment;
use App\Modules\Payments\Services\PaymentReceiptRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
    ) {}

    public function envelope(): Envelope
    {
        $clube = ClubSetting::current()->nome_clube;

        return new Envelope(
            subject: 'Comprovativo de pagamento de quota · '.$clube,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-receipt',
            with: [
                'payment' => $this->payment,
                'member' => $this->payment->member,
                'settings' => ClubSetting::current(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $renderer = app(PaymentReceiptRenderer::class);

        return [
            Attachment::fromData(
                fn (): string => $renderer->pdfOutput($this->payment),
                $renderer->filename($this->payment),
            )->withMime('application/pdf'),
        ];
    }
}
