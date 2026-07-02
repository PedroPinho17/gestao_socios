<?php

namespace App\Modules\Settings\Filament\Resources\ModuleFeatures\Pages;

use App\Modules\Settings\Filament\Resources\ModuleFeatures\ModuleFeatureResource;
use App\Support\FeatureRegistry;
use App\Support\ModuleRegistry;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListModuleFeatures extends ListRecords
{
    protected static string $resource = ModuleFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncCatalog')
                ->label('Sincronizar catálogo')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function (): void {
                    $modules = ModuleRegistry::syncCatalog();
                    $features = FeatureRegistry::syncCatalog();

                    Notification::make()
                        ->title("Sync: +{$modules} módulo(s), +{$features} funcionalidade(s).")
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->visible(fn (): bool => FeatureRegistry::hasMissingCatalogEntries()),
        ];
    }
}
