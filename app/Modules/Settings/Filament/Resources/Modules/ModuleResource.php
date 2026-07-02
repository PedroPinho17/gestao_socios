<?php

namespace App\Modules\Settings\Filament\Resources\Modules;

use App\Models\Module;
use App\Modules\Settings\Filament\Resources\Modules\Pages\CreateModule;
use App\Modules\Settings\Filament\Resources\Modules\Pages\EditModule;
use App\Modules\Settings\Filament\Resources\Modules\Pages\ManageModules;
use App\Modules\Settings\Filament\Resources\Modules\RelationManagers\ModuleFeaturesRelationManager;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;

    protected static ?string $navigationLabel = 'Módulos';

    protected static ?string $modelLabel = 'módulo';

    protected static ?string $pluralModelLabel = 'módulos';

    protected static string|UnitEnum|null $navigationGroup = 'Configuração';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'label';

    public static function canAccess(): bool
    {
        return auth()->user()?->isImperador() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isImperador() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        /** @var Module $record */
        return ! $record->is_core && (auth()->user()?->isImperador() ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('slug')
                    ->label('Identificador')
                    ->required()
                    ->maxLength(64)
                    ->regex('/^[a-z][a-z0-9_]*$/')
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (?Module $record): bool => $record !== null)
                    ->dehydrated(fn (?Module $record): bool => $record === null)
                    ->helperText('Ex.: cashless — só letras minúsculas, números e _. Não muda após criar.'),
                TextInput::make('label')
                    ->label('Nome')
                    ->required()
                    ->maxLength(120),
                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(2)
                    ->columnSpanFull(),
                Textarea::make('disabled_message')
                    ->label('Mensagem quando desactivado')
                    ->rows(2)
                    ->columnSpanFull(),
                Toggle::make('enabled')
                    ->label('Activo')
                    ->default(true)
                    ->disabled(fn (?Module $record): bool => (bool) $record?->is_core)
                    ->helperText(fn (?Module $record): ?string => $record?->is_core
                        ? 'Módulo base — não pode ser desactivado.'
                        : 'Ao desactivar, todas as funcionalidades deste módulo ficam indisponíveis.'),
                TextInput::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Módulo')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Module $record): ?string => $record->slug),
                TextColumn::make('features_count')
                    ->label('Funcionalidades')
                    ->counts('features')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(40)
                    ->toggleable(),
                IconColumn::make('enabled')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_core')
                    ->label('Base')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ModuleFeaturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageModules::route('/'),
            'create' => CreateModule::route('/create'),
            'edit' => EditModule::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $inactive = Module::query()->where('is_core', false)->where('enabled', false)->count();

        return $inactive > 0 ? (string) $inactive : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }
}
