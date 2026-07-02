<?php

namespace App\Modules\Core;

use Filament\Pages\Page;
use Filament\Widgets\Widget;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    public static function filamentResources(): array
    {
        return [];
    }

    /**
     * @return list<class-string<Page>>
     */
    public static function filamentPages(): array
    {
        return [];
    }

    /**
     * @return list<class-string<Widget>>
     */
    public static function filamentWidgets(): array
    {
        return [];
    }
}
