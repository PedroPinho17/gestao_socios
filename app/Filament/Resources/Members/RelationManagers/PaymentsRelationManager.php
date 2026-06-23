<?php

namespace App\Filament\Resources\Members\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Pagamentos de quota';

    protected static ?string $modelLabel = 'pagamento';

    protected static ?string $pluralModelLabel = 'pagamentos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('data')
                    ->label('Data')
                    ->required()
                    ->default(now()),
                TextInput::make('valor')
                    ->label('Valor (€)')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01),
                TextInput::make('referencia')
                    ->label('Referência')
                    ->placeholder('Ex.: 2026-01')
                    ->required()
                    ->maxLength(50),
                Textarea::make('notas')
                    ->label('Notas')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 2, ',', ' ').' €'),
                TextColumn::make('referencia')
                    ->label('Referência'),
                TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(40),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Registar pagamento')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (blank($data['referencia'] ?? null) && filled($data['data'] ?? null)) {
                            $data['referencia'] = \Carbon\Carbon::parse($data['data'])->format('Y-m');
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('data', 'desc');
    }
}
