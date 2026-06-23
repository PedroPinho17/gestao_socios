<?php

namespace App\Services;

use App\Enums\Periodicidade;
use App\Enums\QuotaSituationKind;
use App\Enums\TipoVencimentoQuota;
use App\Models\AppSetting;
use App\Models\Member;
use App\Models\Payment;
use App\Models\QuotaPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class QuotaService
{
    /** @var array<string, mixed>|null */
    private static ?array $situationDataCache = null;

    private ?int $dueSoonDaysCache = null;

    public static function clearSituationCache(): void
    {
        static::$situationDataCache = null;
    }

    public function dueSoonDays(): int
    {
        return $this->dueSoonDaysCache ??= max(1, AppSetting::int(AppSetting::DIAS_ALERTA_QUOTA, 7));
    }

    /**
     * @return array{
     *     kind: QuotaSituationKind,
     *     next_due: ?Carbon,
     *     days_overdue: ?int,
     *     days_until: ?int
     * }
     */
    public function getSituation(Member $member, ?Carbon $now = null): array
    {
        $now = ($now ?? now())->startOfDay();

        if (! $member->ativo) {
            return $this->situation(QuotaSituationKind::Inativo);
        }

        $plan = $member->quotaPlan;
        if (! $plan) {
            return $this->situation(QuotaSituationKind::SemPlano);
        }

        $payments = $member->relationLoaded('payments')
            ? $member->payments
            : $member->payments()->get();

        $lastPayment = $payments->first();
        $base = $lastPayment?->data ?? $member->data_adesao;

        if (! $base) {
            return $this->situation(QuotaSituationKind::SemPlano);
        }

        $nextDue = $this->computeNextDueDate($plan, $base)->startOfDay();

        if ($now->gt($nextDue)) {
            return $this->situation(
                QuotaSituationKind::Overdue,
                $nextDue,
                daysOverdue: (int) $nextDue->diffInDays($now),
            );
        }

        $daysUntil = (int) $now->diffInDays($nextDue);

        if ($daysUntil <= $this->dueSoonDays()) {
            return $this->situation(
                QuotaSituationKind::DueSoon,
                $nextDue,
                daysUntil: $daysUntil,
            );
        }

        return $this->situation(QuotaSituationKind::Ok, $nextDue);
    }

    public function computeNextDueDate(QuotaPlan $plan, Carbon $base): Carbon
    {
        $months = $plan->periodicidade->periodMonths();

        if ($plan->tipo_vencimento !== TipoVencimentoQuota::DiaFixo) {
            return $base->copy()->addMonths($months);
        }

        $dia = min(max((int) $plan->dia_vencimento_mes, 1), 31);
        $target = $base->copy()->addMonths($months);
        $lastDay = $target->copy()->endOfMonth()->day;

        return $target->setDay(min($dia, $lastDay));
    }

    public function resumoVencimentoPlano(QuotaPlan $plan): string
    {
        if ($plan->tipo_vencimento === TipoVencimentoQuota::DiaFixo) {
            return 'Dia '.$plan->dia_vencimento_mes;
        }

        return 'Aniversário';
    }

    public function formatSituationLabel(array $situation): string
    {
        return match ($situation['kind']) {
            QuotaSituationKind::Inativo => 'Inativo',
            QuotaSituationKind::SemPlano => 'Sem plano',
            QuotaSituationKind::Overdue => sprintf(
                'Atraso %d d. · %s',
                $situation['days_overdue'],
                $this->formatDatePT($situation['next_due']),
            ),
            QuotaSituationKind::DueSoon => sprintf(
                'Em %d d. · %s',
                $situation['days_until'],
                $this->formatDatePT($situation['next_due']),
            ),
            QuotaSituationKind::Ok => 'Em dia · '.$this->formatDatePT($situation['next_due']),
        };
    }

    public function formatDatePT(?Carbon $date): string
    {
        return $date?->format('d/m/Y') ?? '—';
    }

    /**
     * @return array{overdue: Collection, dueSoon: Collection}
     */
    public function alertLists(): array
    {
        $data = $this->cachedSituationData();

        return [
            'overdue' => $data['overdue'],
            'dueSoon' => $data['dueSoon'],
        ];
    }

    /**
     * @return Collection<int, Member>
     */
    public function membersWithSituation(QuotaSituationKind $kind): Collection
    {
        $ids = $this->cachedSituationData()['idsByKind'][$kind->value] ?? [];

        if ($ids === []) {
            return collect();
        }

        return Member::query()
            ->with(['quotaPlan', 'payments'])
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * @return Collection<int, int>
     */
    public function filterMemberIdsBySituation(?string $filter): Collection
    {
        if (blank($filter) || $filter === 'all') {
            return collect();
        }

        $kind = match ($filter) {
            'overdue' => QuotaSituationKind::Overdue,
            'due_soon' => QuotaSituationKind::DueSoon,
            'ok' => QuotaSituationKind::Ok,
            'inativo' => QuotaSituationKind::Inativo,
            'sem_plano' => QuotaSituationKind::SemPlano,
            default => null,
        };

        if (! $kind) {
            return collect();
        }

        return collect($this->cachedSituationData()['idsByKind'][$kind->value] ?? []);
    }

    /**
     * @return array{
     *     overdue: Collection,
     *     dueSoon: Collection,
     *     idsByKind: array<string, list<int>>
     * }
     */
    private function cachedSituationData(): array
    {
        if (static::$situationDataCache !== null) {
            return static::$situationDataCache;
        }

        return static::$situationDataCache = $this->buildSituationData();
    }

    /**
     * @return array{
     *     overdue: Collection,
     *     dueSoon: Collection,
     *     idsByKind: array<string, list<int>>
     * }
     */
    private function buildSituationData(): array
    {
        $overdue = collect();
        $dueSoon = collect();
        $idsByKind = [];

        foreach ($this->membersWithRelations() as $member) {
            $situation = $this->getSituation($member);
            $kind = $situation['kind']->value;
            $idsByKind[$kind] ??= [];
            $idsByKind[$kind][] = $member->id;

            if ($situation['kind'] === QuotaSituationKind::Overdue) {
                $overdue->push([
                    'id' => $member->id,
                    'nome' => $member->nome,
                    'situation' => $situation,
                ]);
            } elseif ($situation['kind'] === QuotaSituationKind::DueSoon) {
                $dueSoon->push([
                    'id' => $member->id,
                    'nome' => $member->nome,
                    'situation' => $situation,
                ]);
            }
        }

        return [
            'overdue' => $overdue->sortByDesc(fn ($row) => $row['situation']['days_overdue'])->values(),
            'dueSoon' => $dueSoon->sortBy(fn ($row) => $row['situation']['days_until'])->values(),
            'idsByKind' => $idsByKind,
        ];
    }

    /**
     * @return Collection<int, Member>
     */
    private function membersWithRelations(): Collection
    {
        return Member::query()
            ->with(['quotaPlan', 'payments'])
            ->get();
    }

    /**
     * @return array{
     *     kind: QuotaSituationKind,
     *     next_due: ?Carbon,
     *     days_overdue: ?int,
     *     days_until: ?int
     * }
     */
    private function situation(
        QuotaSituationKind $kind,
        ?Carbon $nextDue = null,
        ?int $daysOverdue = null,
        ?int $daysUntil = null,
    ): array {
        return [
            'kind' => $kind,
            'next_due' => $nextDue,
            'days_overdue' => $daysOverdue,
            'days_until' => $daysUntil,
        ];
    }
}
