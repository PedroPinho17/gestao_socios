<?php

namespace App\Filament\Resources\QuotaPlans;

use App\Enums\Periodicidade;
use App\Enums\TipoVencimentoQuota;
use App\Filament\Resources\QuotaPlans\Pages\ManageQuotaPlans;
use App\Models\QuotaPlan;
use App\Services\QuotaService;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class QuotaPlanResource extends Resource
{
    protected static ?string $model = QuotaPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Planos de quota';

    protected static ?string $modelLabel = 'plano';

    protected static ?string $pluralModelLabel = 'planos de quota';

    protected static string|UnitEnum|null $navigationGroup = 'Gestão';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nome';

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageClub() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->canManageClub() ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->canManageClub() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome do plano')
                    ->required()
                    ->maxLength(255),
                Select::make('periodicidade')
                    ->label('Periodicidade')
                    ->options(collect(Periodicidade::cases())->mapWithKeys(
                        fn (Periodicidade $p) => [$p->value => $p->label()],
                    ))
                    ->required()
                    ->default(Periodicidade::Mensal->value),
                TextInput::make('valor')
                    ->label('Valor (€)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                Select::make('tipo_vencimento')
                    ->label('Regra do vencimento')
                    ->options(collect(TipoVencimentoQuota::cases())->mapWithKeys(
                        fn (TipoVencimentoQuota $t) => [$t->value => $t->label()],
                    ))
                    ->required()
                    ->default(TipoVencimentoQuota::Aniversario->value)
                    ->live(),
                TextInput::make('dia_vencimento_mes')
                    ->label('Dia do mês (1–31)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->default(1)
                    ->visible(fn (Get $get): bool => $get('tipo_vencimento') === TipoVencimentoQuota::DiaFixo->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('periodicidade')
                    ->label('Periodicidade')
                    ->formatStateUsing(fn (Periodicidade $state): string => $state->label()),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', ' ').' €'),
                TextColumn::make('tipo_vencimento')
                    ->label('Vencimento')
                    ->formatStateUsing(fn (QuotaPlan $record): string => app(QuotaService::class)->resumoVencimentoPlano($record)),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageQuotaPlans::route('/'),
        ];
    }
}
