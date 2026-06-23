<?php

namespace App\Services;

use App\Enums\QuotaSituationKind;
use App\Models\Member;
use Illuminate\Support\Collection;

class OverdueMembersReportService
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
     *     dias_atraso: int,
     *     vencimento: string
     * }>
     */
    public function rows(): Collection
    {
        return $this->quotaService
            ->membersWithSituation(QuotaSituationKind::Overdue)
            ->map(function (Member $member): array {
                $situation = $member->quotaSituation();

                return [
                    'numero' => $member->numero,
                    'nome' => $member->nome,
                    'email' => $member->email,
                    'telefone' => $member->telefone,
                    'plano' => $member->quotaPlan?->nome,
                    'dias_atraso' => (int) ($situation['days_overdue'] ?? 0),
                    'vencimento' => $this->quotaService->formatDatePT($situation['next_due'] ?? null),
                ];
            })
            ->sortByDesc('dias_atraso')
            ->values();
    }
}
