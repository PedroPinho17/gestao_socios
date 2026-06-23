<?php

namespace App\Filament\Widgets;

use App\Services\QuotaService;
use Filament\Widgets\Widget;

class QuotaAlertsWidget extends Widget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.quota-alerts';

    protected function getViewData(): array
    {
        $service = app(QuotaService::class);
        $lists = $service->alertLists();

        return [
            'overdue' => $lists['overdue']->take(20),
            'dueSoon' => $lists['dueSoon']->take(20),
            'overdueTotal' => $lists['overdue']->count(),
            'dueSoonTotal' => $lists['dueSoon']->count(),
            'dueSoonDays' => $service->dueSoonDays(),
            'quotaService' => $service,
        ];
    }
}
