<?php

namespace App\Modules\Notifications;

use App\Modules\Core\ModuleServiceProvider;
use App\Modules\Notifications\Filament\Pages\CommunicationsPage;
use Filament\Pages\Page;

class NotificationsServiceProvider extends ModuleServiceProvider
{
    /**
     * @return list<class-string<Page>>
     */
    public static function filamentPages(): array
    {
        return [
            CommunicationsPage::class,
        ];
    }
}
