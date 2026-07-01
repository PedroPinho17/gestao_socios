<?php

namespace App\Filament\Clusters;

use App\Filament\Concerns\RequiresModuleFeature;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CatalogosCluster extends Cluster
{
    use RequiresModuleFeature;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Catálogos';

    protected static ?string $title = 'Catálogos';

    protected static string|UnitEnum|null $navigationGroup = 'Configuração';

    protected static ?int $navigationSort = 7;

    protected static function moduleFeatureKey(): string
    {
        return 'filament.catalogos';
    }

    protected static function authorizeModuleFeatureAccess(): bool
    {
        return auth()->user()?->canManageClub() ?? false;
    }
}
