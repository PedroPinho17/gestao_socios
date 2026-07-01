<?php

namespace App\Filament\Concerns;

use App\Support\ModuleRegistry;

trait RequiresModule
{
    abstract protected static function moduleSlug(): string;

    public static function canAccess(): bool
    {
        if (! ModuleRegistry::enabled(static::moduleSlug())) {
            return false;
        }

        return static::authorizeModuleAccess();
    }

    protected static function authorizeModuleAccess(): bool
    {
        return true;
    }
}
