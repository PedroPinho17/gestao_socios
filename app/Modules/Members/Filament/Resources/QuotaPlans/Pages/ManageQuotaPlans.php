<?php

namespace App\Modules\Members\Filament\Resources\QuotaPlans\Pages;

use App\Modules\Members\Filament\Resources\QuotaPlans\QuotaPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageQuotaPlans extends ManageRecords
{
    protected static string $resource = QuotaPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Adicionar plano')
                ->visible(fn (): bool => QuotaPlanResource::canCreate()),
        ];
    }
}
