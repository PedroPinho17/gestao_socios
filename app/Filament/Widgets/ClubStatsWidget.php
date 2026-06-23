<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use App\Models\QuotaPlan;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClubStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = true;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $memberStats = Member::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos')
            ->first();

        $planos = QuotaPlan::count();

        return [
            Stat::make('Sócios registados', (string) ($memberStats->total ?? 0)),
            Stat::make('Sócios ativos', (string) ($memberStats->ativos ?? 0))
                ->color('success'),
            Stat::make('Tipos de quota', (string) $planos),
        ];
    }
}
