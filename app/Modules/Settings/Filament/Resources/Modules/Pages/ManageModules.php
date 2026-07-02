<?php

namespace App\Modules\Settings\Filament\Resources\Modules\Pages;

use App\Modules\Settings\Filament\Resources\Modules\ModuleResource;
use App\Support\FeatureRegistry;
use App\Support\ModuleRegistry;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageModules extends ManageRecords
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncCatalog')
                ->label('Sincronizar catálogo')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Sincronizar módulos e funcionalidades')
                ->modalDescription('Importa do código módulos e funcionalidades novos (actualizações da app). Não altera registos existentes.')
                ->action(function (): void {
                    $modules = ModuleRegistry::syncCatalog();
                    $features = FeatureRegistry::syncCatalog();

                    Notification::make()
                        ->title("Sync concluído: +{$modules} módulo(s), +{$features} funcionalidade(s).")
                        ->success()
                        ->send();
                }),
            CreateAction::make()
                ->label('Novo módulo'),
        ];
    }
}
