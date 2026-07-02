<?php

namespace App\Modules\Audit;

use App\Modules\Audit\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Modules\Core\ModuleServiceProvider;

class AuditServiceProvider extends ModuleServiceProvider
{
    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    public static function filamentResources(): array
    {
        return [
            ActivityLogResource::class,
        ];
    }
}
