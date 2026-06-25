<?php

namespace App\Http\Resources;

use App\Models\Payment;
use App\Services\QuotaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payment */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $quotaService = app(QuotaService::class);

        return [
            'id' => $this->id,
            'data' => $this->data?->toDateString(),
            'data_formatted' => $quotaService->formatDatePT($this->data),
            'valor' => (float) $this->valor,
            'valor_formatted' => number_format((float) $this->valor, 2, ',', ' ').' €',
            'referencia' => $this->referencia,
            'notas' => $this->notas,
        ];
    }
}
