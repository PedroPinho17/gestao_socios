<?php

namespace App\Filament\Resources\TiposVencimentoQuota\Pages;

use App\Filament\Resources\TiposVencimentoQuota\TipoVencimentoQuotaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTiposVencimentoQuota extends ManageRecords
{
    protected static string $resource = TipoVencimentoQuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar tipo de vencimento'),
        ];
    }
}
