<?php

namespace App\Http\Resources;

use App\Models\Member;
use App\Services\QuotaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotaStatusResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{situation: array, member: Member} $data */
        $data = $this->resource;
        $situation = $data['situation'];
        $member = $data['member'];
        $quotaService = app(QuotaService::class);
        $plan = $member->quotaPlan;

        return [
            'status' => $situation['kind']->value,
            'label' => $quotaService->formatSituationLabel($situation),
            'next_due' => $situation['next_due']?->toDateString(),
            'next_due_formatted' => $quotaService->formatDatePT($situation['next_due']),
            'days_overdue' => $situation['days_overdue'],
            'days_until' => $situation['days_until'],
            'plan' => $plan ? [
                'nome' => $plan->nome,
                'valor' => (float) $plan->valor,
                'periodicidade' => $plan->periodicidade?->nome,
            ] : null,
        ];
    }
}
