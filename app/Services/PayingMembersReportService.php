<?php

namespace App\Services;

use App\Enums\QuotaSituationKind;
use App\Models\Member;
use Illuminate\Support\Collection;

class PayingMembersReportService
{
    public function __construct(
        private readonly QuotaService $quotaService,
    ) {}

    /**
     * @return Collection<int, array{
     *     numero: string,
     *     nome: string,
     *     email: ?string,
     *     telefone: ?string,
     *     plano: ?string,
     *     situacao: string,
     *     vencimento: string,
     *     vencimento_sort: ?string
     * }>
     */
    public function rows(): Collection
    {
        return $this->quotaService
            ->membersWithSituation(QuotaSituationKind::Ok)
            ->merge($this->quotaService->membersWithSituation(QuotaSituationKind::DueSoon))
            ->map(function (Member $member): array {
                $situation = $member->quotaSituation();
                $nextDue = $situation['next_due'] ?? null;

                return [
                    'numero' => $member->numero,
                    'nome' => $member->nome,
                    'email' => $member->email,
                    'telefone' => $member->telefone,
                    'plano' => $member->quotaPlan?->nome,
                    'situacao' => $situation['kind'] === QuotaSituationKind::DueSoon ? 'A vencer' : 'Em dia',
                    'vencimento' => $this->quotaService->formatDatePT($nextDue),
                    'vencimento_sort' => $nextDue?->format('Y-m-d'),
                ];
            })
            ->sortBy('vencimento_sort')
            ->values();
    }
}
