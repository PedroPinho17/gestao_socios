<?php

namespace App\Modules\Catalogos\Filament\Resources\Periodicidades;

use App\Models\Periodicidade;
use App\Modules\Catalogos\Filament\Clusters\CatalogosCluster;
use App\Modules\Catalogos\Filament\Resources\Periodicidades\Pages\ManagePeriodicidades;
use App\Modules\Core\Filament\Concerns\RequiresModuleFeature;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PeriodicidadeResource extends Resource
{
    use RequiresModuleFeature;

    protected static ?string $model = Periodicidade::class;

    protected static ?string $cluster = CatalogosCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Periodicidades';

    protected static ?string $modelLabel = 'periodicidade';

    protected static ?string $pluralModelLabel = 'periodicidades';

    protected static ?string $recordTitleAttribute = 'nome';

    protected static ?int $navigationSort = 1;

    protected static function moduleFeatureKey(): string
    {
        return 'filament.periodicidades';
    }

    protected static function authorizeModuleFeatureAccess(): bool
    {
        return auth()->user()?->canManageClub() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Identificador')
                    ->required()
                    ->maxLength(30)
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (?Periodicidade $record): bool => $record !== null)
                    ->helperText('Código interno (ex.: mensal). Não alterável após criação.'),
                TextInput::make('nome')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('meses')
                    ->label('Meses por período')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(120)
                    ->helperText('Usado no cálculo do próximo vencimento da quota.'),
                TextInput::make('ordem')
                    ->label('Ordem')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Identificador')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('meses')
                    ->label('Meses')
                    ->sortable(),
                TextColumn::make('quota_plans_count')
                    ->label('Planos')
                    ->counts('quotaPlans'),
                TextColumn::make('ordem')
                    ->label('Ordem')
                    ->sortable(),
            ])
            ->defaultSort('ordem')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Periodicidade $record): void {
                        if ($record->quotaPlans()->exists()) {
                            Notification::make()
                                ->title('Não é possível eliminar')
                                ->body('Esta periodicidade está associada a planos de quota.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->canManageClub() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePeriodicidades::route('/'),
        ];
    }
}
