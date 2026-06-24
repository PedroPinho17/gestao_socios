<?php

namespace App\Filament\Resources\Periodicidades\Pages;

use App\Filament\Resources\Periodicidades\PeriodicidadeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePeriodicidades extends ManageRecords
{
    protected static string $resource = PeriodicidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar periodicidade'),
        ];
    }
}
