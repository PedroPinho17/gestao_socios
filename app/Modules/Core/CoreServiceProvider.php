<?php

namespace App\Modules\Core;

use App\Modules\Core\Filament\Pages\ChangeRequiredPassword;
use App\Modules\Core\Filament\Pages\Dashboard;
use Filament\Pages\Page;

class CoreServiceProvider extends ModuleServiceProvider
{
    /**
     * @return list<class-string<Page>>
     */
    public static function filamentPages(): array
    {
        return [
            Dashboard::class,
            ChangeRequiredPassword::class,
        ];
    }
}
