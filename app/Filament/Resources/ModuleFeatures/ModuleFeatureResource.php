<?php

namespace App\Filament\Resources\ModuleFeatures;

use App\Filament\Resources\ModuleFeatures\Pages\CreateModuleFeature;
use App\Filament\Resources\ModuleFeatures\Pages\EditModuleFeature;
use App\Filament\Resources\ModuleFeatures\Pages\ListModuleFeatures;
use App\Models\Module;
use App\Models\ModuleFeature;
use App\Support\FeatureRegistry;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ModuleFeatureResource extends Resource
{
    protected static ?string $model = ModuleFeature::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?string $navigationLabel = 'Funcionalidades';

    protected static ?string $modelLabel = 'funcionalidade';

    protected static ?string $pluralModelLabel = 'funcionalidades';

    protected static string|UnitEnum|null $navigationGroup = 'Configuração';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'label';

    public static function canAccess(): bool
    {
        return auth()->user()?->isImperador() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('catalog_empty_notice')
                    ->hiddenLabel()
                    ->content('Todas as funcionalidades conhecidas já estão registadas. Use «Sincronizar catálogo» na listagem quando o programador entregar uma actualização do sistema.')
                    ->visible(fn (string $operation): bool => $operation === 'create' && ! FeatureRegistry::hasMissingCatalogEntries()),

                Select::make('key')
                    ->label('Funcionalidade')
                    ->helperText('Escolha da lista instalada pelo programador — não precisa de escrever códigos.')
                    ->options(fn (): array => FeatureRegistry::missingCatalogSelectOptions())
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(fn (string $operation): bool => $operation === 'create' && FeatureRegistry::hasMissingCatalogEntries())
                    ->visible(fn (string $operation): bool => $operation === 'create' && FeatureRegistry::hasMissingCatalogEntries())
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        if (blank($state)) {
                            return;
                        }

                        $defaults = FeatureRegistry::catalogFormDefaults($state);

                        if ($defaults === null) {
                            return;
                        }

                        foreach ($defaults as $field => $value) {
                            $set($field, $value);
                        }
                    }),

                Select::make('module_id')
                    ->label('Módulo')
                    ->helperText('A que pacote pertence (ex.: Sócios, Catálogos, Cashless).')
                    ->options(fn (): array => Module::selectOptions())
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->visible(fn (string $operation): bool => $operation === 'edit' || FeatureRegistry::hasMissingCatalogEntries()),

                TextInput::make('key')
                    ->label('Referência interna')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->helperText('Identificador fixo definido pelo sistema; não precisa de alterar.'),

                TextInput::make('label')
                    ->label('Nome')
                    ->required()
                    ->maxLength(160)
                    ->visible(fn (string $operation): bool => $operation === 'edit' || FeatureRegistry::hasMissingCatalogEntries()),

                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(2)
                    ->columnSpanFull()
                    ->visible(fn (string $operation): bool => $operation === 'edit' || FeatureRegistry::hasMissingCatalogEntries()),

                TextInput::make('binding_type')
                    ->hidden()
                    ->dehydrated(),

                TextInput::make('binding_target')
                    ->hidden()
                    ->dehydrated(),

                TextInput::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->default(0)
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Funcionalidade')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ModuleFeature $record): string => $record->key),
                TextColumn::make('module.label')
                    ->label('Módulo')
                    ->sortable()
                    ->description(fn (ModuleFeature $record): ?string => $record->module?->slug),
                TextColumn::make('binding_type')
                    ->label('Tipo')
                    ->badge(),
                IconColumn::make('is_system')
                    ->label('Sistema')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function canCreate(): bool
    {
        return (auth()->user()?->isImperador() ?? false) && FeatureRegistry::hasMissingCatalogEntries();
    }

    public static function canDelete(Model $record): bool
    {
        return ! $record->is_system && (auth()->user()?->isImperador() ?? false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModuleFeatures::route('/'),
            'create' => CreateModuleFeature::route('/create'),
            'edit' => EditModuleFeature::route('/{record}/edit'),
        ];
    }
}
