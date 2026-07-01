<?php

namespace App\Filament\Concerns;

use App\Support\FeatureRegistry;

trait RequiresModuleFeature
{
    abstract protected static function moduleFeatureKey(): string;

    public static function canAccess(): bool
    {
        if (! FeatureRegistry::enabled(static::moduleFeatureKey())) {
            return false;
        }

        return static::authorizeModuleFeatureAccess();
    }

    protected static function authorizeModuleFeatureAccess(): bool
    {
        return true;
    }
}
