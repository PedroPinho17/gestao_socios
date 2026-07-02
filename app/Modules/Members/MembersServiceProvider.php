<?php

namespace App\Modules\Members;

use App\Modules\Core\ModuleServiceProvider;
use App\Modules\Members\Filament\Resources\Members\MemberResource;
use App\Modules\Members\Filament\Resources\QuotaPlans\QuotaPlanResource;
use App\Modules\Members\Filament\Widgets\ClubStatsWidget;
use App\Modules\Members\Filament\Widgets\QuotaAlertsWidget;
use Filament\Widgets\Widget;

class MembersServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    public static function filamentResources(): array
    {
        return [
            MemberResource::class,
            QuotaPlanResource::class,
        ];
    }

    /**
     * @return list<class-string<Widget>>
     */
    public static function filamentWidgets(): array
    {
        return [
            ClubStatsWidget::class,
            QuotaAlertsWidget::class,
        ];
    }
}
