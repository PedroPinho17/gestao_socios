<?php

namespace App\Modules\Members\Filament\Resources\QuotaPlans;

use App\Models\Periodicidade;
use App\Models\QuotaPlan;
use App\Models\TipoVencimentoQuota;
use App\Modules\Core\Filament\Concerns\RequiresModuleFeature;
use App\Modules\Members\Filament\Resources\QuotaPlans\Pages\ManageQuotaPlans;
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
    use RequiresModuleFeature;

    protected static ?string $model = QuotaPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Planos de quota';

    protected static ?string $modelLabel = 'plano';

    protected static ?string $pluralModelLabel = 'planos de quota';

    protected static string|UnitEnum|null $navigationGroup = 'Gestão';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nome';

    protected static function moduleFeatureKey(): string
    {
        return 'filament.quota_plans';
    }

    protected static function authorizeModuleFeatureAccess(): bool
    {
        return auth()->check();
    }

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
                Select::make('periodicidade_id')
                    ->label('Periodicidade')
                    ->options(fn (): array => Periodicidade::optionsForSelect())
                    ->required()
                    ->default(fn (): int => (int) (Periodicidade::query()->where('slug', 'mensal')->value('id') ?? 1)),
                TextInput::make('valor')
                    ->label('Valor (€)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01),
                Select::make('tipo_vencimento_quota_id')
                    ->label('Regra do vencimento')
                    ->options(fn (): array => TipoVencimentoQuota::optionsForSelect())
                    ->required()
                    ->default(fn (): int => (int) (TipoVencimentoQuota::query()->where('slug', 'aniversario')->value('id') ?? 1))
                    ->live(),
                TextInput::make('dia_vencimento_mes')
                    ->label('Dia do mês (1–31)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->default(1)
                    ->visible(function (Get $get): bool {
                        $tipoId = $get('tipo_vencimento_quota_id');

                        if (! $tipoId) {
                            return false;
                        }

                        return TipoVencimentoQuota::query()
                            ->whereKey($tipoId)
                            ->where('slug', 'dia_fixo')
                            ->exists();
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['periodicidade', 'tipoVencimento']))
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('periodicidade.nome')
                    ->label('Periodicidade'),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', ' ').' €'),
                TextColumn::make('tipoVencimento.nome')
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
