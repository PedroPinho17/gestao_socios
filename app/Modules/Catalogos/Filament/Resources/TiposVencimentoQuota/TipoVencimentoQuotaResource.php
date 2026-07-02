<?php

namespace App\Modules\Catalogos\Filament\Resources\TiposVencimentoQuota;

use App\Models\TipoVencimentoQuota;
use App\Modules\Catalogos\Filament\Clusters\CatalogosCluster;
use App\Modules\Catalogos\Filament\Resources\TiposVencimentoQuota\Pages\ManageTiposVencimentoQuota;
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

class TipoVencimentoQuotaResource extends Resource
{
    use RequiresModuleFeature;

    protected static ?string $model = TipoVencimentoQuota::class;

    protected static ?string $cluster = CatalogosCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Tipos de vencimento';

    protected static ?string $modelLabel = 'tipo de vencimento';

    protected static ?string $pluralModelLabel = 'tipos de vencimento';

    protected static ?string $recordTitleAttribute = 'nome';

    protected static ?int $navigationSort = 2;

    protected static function moduleFeatureKey(): string
    {
        return 'filament.tipos_vencimento';
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
                    ->disabled(fn (?TipoVencimentoQuota $record): bool => $record !== null)
                    ->helperText('Código interno (ex.: dia_fixo). Não alterável após criação.'),
                TextInput::make('nome')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
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
                    ->before(function (DeleteAction $action, TipoVencimentoQuota $record): void {
                        if ($record->quotaPlans()->exists()) {
                            Notification::make()
                                ->title('Não é possível eliminar')
                                ->body('Este tipo de vencimento está associado a planos de quota.')
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
            'index' => ManageTiposVencimentoQuota::route('/'),
        ];
    }
}
