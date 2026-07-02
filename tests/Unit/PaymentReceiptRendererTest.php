<?php

namespace Tests\Unit;

use App\Modules\Members\Services\QuotaService;
use App\Modules\Payments\Services\PaymentReceiptRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class PaymentReceiptRendererTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    public function test_receipt_number_uses_payment_year_and_id(): void
    {
        $member = $this->createMember();
        $payment = $this->createPayment($member, [
            'data' => '2026-03-15',
            'referencia' => '2026-03',
        ]);

        $renderer = app(PaymentReceiptRenderer::class);

        $this->assertSame('2026-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT), $renderer->receiptNumber($payment));
        $this->assertSame('comprovativo_'.$renderer->receiptNumber($payment).'.pdf', $renderer->filename($payment));
    }

    public function test_pdf_output_generates_non_empty_document(): void
    {
        $member = $this->createMember(['nome' => 'Recibo Teste']);
        $payment = $this->createPayment($member);

        $output = app(PaymentReceiptRenderer::class)->pdfOutput($payment);

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF', $output);
    }

    protected function tearDown(): void
    {
        QuotaService::clearSituationCache();
        parent::tearDown();
    }
}
