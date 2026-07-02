<?php

namespace App\Modules\Settings\Filament\Resources\ModuleFeatures\Pages;

use App\Modules\Settings\Filament\Resources\ModuleFeatures\ModuleFeatureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditModuleFeature extends EditRecord
{
    protected static string $resource = ModuleFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->record->is_system),
        ];
    }
}
