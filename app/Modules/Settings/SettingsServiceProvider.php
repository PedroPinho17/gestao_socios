<?php

namespace App\Modules\Settings;

use App\Modules\Core\ModuleServiceProvider;
use App\Modules\Settings\Filament\Pages\ClubSettingsPage;
use App\Modules\Settings\Filament\Pages\SystemSettingsPage;
use App\Modules\Settings\Filament\Resources\ModuleFeatures\ModuleFeatureResource;
use App\Modules\Settings\Filament\Resources\Modules\ModuleResource;
use Filament\Pages\Page;

class SettingsServiceProvider extends ModuleServiceProvider
{
    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    public static function filamentResources(): array
    {
        return [
            ModuleResource::class,
            ModuleFeatureResource::class,
        ];
    }

    /**
     * @return list<class-string<Page>>
     */
    public static function filamentPages(): array
    {
        return [
            ClubSettingsPage::class,
            SystemSettingsPage::class,
        ];
    }
}
