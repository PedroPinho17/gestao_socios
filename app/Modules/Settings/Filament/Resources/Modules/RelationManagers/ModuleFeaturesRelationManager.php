<?php

namespace App\Modules\Settings\Filament\Resources\Modules\RelationManagers;

use App\Models\ModuleFeature;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ModuleFeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'features';

    protected static ?string $title = 'Funcionalidades / páginas';

    protected static ?string $modelLabel = 'funcionalidade';

    protected static ?string $pluralModelLabel = 'funcionalidades';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Identificador')
                    ->required()
                    ->maxLength(120)
                    ->regex('/^[a-z][a-z0-9._-]*$/')
                    ->helperText('Ex.: cashless.wallets ou filament.cashless.transactions')
                    ->unique(
                        table: ModuleFeature::class,
                        column: 'key',
                        ignoreRecord: true,
                    ),
                TextInput::make('label')
                    ->label('Nome')
                    ->required()
                    ->maxLength(160),
                Textarea::make('description')
                    ->label('Descrição')
                    ->rows(2)
                    ->columnSpanFull(),
                Select::make('binding_type')
                    ->label('Tipo')
                    ->options([
                        'filament_page' => 'Página Filament',
                        'filament_resource' => 'Recurso Filament',
                        'filament_cluster' => 'Grupo Filament',
                        'route_group' => 'Rotas web',
                        'api_group' => 'API',
                        'command' => 'Comando / cron',
                        'custom' => 'Personalizado',
                    ])
                    ->native(false),
                TextInput::make('binding_target')
                    ->label('Referência técnica')
                    ->maxLength(255)
                    ->helperText('Classe PHP, nome de rota ou comando. Usado como documentação para o programador.'),
                TextInput::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->default(0),
                Toggle::make('enabled')
                    ->label('Activada')
                    ->helperText('Só tem efeito se o módulo pai estiver activo.')
                    ->default(true)
                    ->disabled(fn (?ModuleFeature $record): bool => (bool) $record?->is_core)
                    ->dehydrated(fn (?ModuleFeature $record): bool => ! (bool) $record?->is_core),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Funcionalidade')
                    ->searchable()
                    ->description(fn (ModuleFeature $record): string => $record->key),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->state(fn (ModuleFeature $record): string => $record->statusBadge()['label'])
                    ->color(fn (ModuleFeature $record): string => $record->statusBadge()['color']),
                ToggleColumn::make('enabled')
                    ->label('Activada')
                    ->disabled(fn (ModuleFeature $record): bool => ! $record->canToggleEnabled()),
                TextColumn::make('binding_type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('binding_target')
                    ->label('Referência')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar funcionalidade'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (ModuleFeature $record): bool => ! $record->is_system),
            ]);
    }
}
