<?php

namespace App\Modules\Catalogos;

use App\Modules\Catalogos\Filament\Clusters\CatalogosCluster;
use App\Modules\Catalogos\Filament\Resources\Periodicidades\PeriodicidadeResource;
use App\Modules\Catalogos\Filament\Resources\TiposVencimentoQuota\TipoVencimentoQuotaResource;
use App\Modules\Core\ModuleServiceProvider;
use Filament\Pages\Page;

class CatalogosServiceProvider extends ModuleServiceProvider
{
    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    public static function filamentResources(): array
    {
        return [
            PeriodicidadeResource::class,
            TipoVencimentoQuotaResource::class,
        ];
    }

    /**
     * @return list<class-string<Page>>
     */
    public static function filamentPages(): array
    {
        return [
            CatalogosCluster::class,
        ];
    }
}
