<?php

namespace App\Filament\Resources\Members\Tables;

use App\Models\Member;
use App\Services\QuotaService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['quotaPlan.periodicidade', 'quotaPlan.tipoVencimento', 'payments']))
            ->columns([
                TextColumn::make('numero')
                    ->label('N.º')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nome')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('quotaPlan.nome')
                    ->label('Plano')
                    ->description(fn (Member $record): ?string => $record->quotaPlan
                        ? $record->quotaPlan->periodicidade->nome.' · '.number_format((float) $record->quotaPlan->valor, 2, ',', ' ').' €'
                        : null),
                TextColumn::make('quota_status')
                    ->label('Pagamento')
                    ->state(fn (Member $record): string => app(QuotaService::class)->formatSituationLabel(
                        $record->quotaSituation(),
                    ))
                    ->color(fn (Member $record): string => match ($record->quotaSituation()['kind']->value) {
                        'overdue' => 'danger',
                        'due_soon' => 'warning',
                        'ok' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('quota_status')
                    ->label('Estado da quota')
                    ->options([
                        'ok' => 'Em dia (pagou)',
                        'due_soon' => 'Vence em breve',
                        'overdue' => 'Em atraso (deve)',
                        'sem_plano' => 'Sem plano',
                        'inativo' => 'Inativo',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $ids = app(QuotaService::class)->filterMemberIdsBySituation($data['value'] ?? null);

                        if ($ids->isEmpty() && filled($data['value'] ?? null)) {
                            return $query->whereRaw('0 = 1');
                        }

                        if ($ids->isNotEmpty()) {
                            return $query->whereIn('id', $ids);
                        }

                        return $query;
                    }),
                SelectFilter::make('ativo')
                    ->label('Estado')
                    ->options([
                        '1' => 'Ativos',
                        '0' => 'Inativos',
                    ]),
            ])
            ->recordActions([
                Action::make('cartao')
                    ->label('Cartão')
                    ->icon('heroicon-o-identification')
                    ->url(fn (Member $record): string => route('member.card', $record))
                    ->openUrlInNewTab(),
                Action::make('cartao_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Member $record): string => route('member.card.pdf', $record))
                    ->openUrlInNewTab(),
                Action::make('cartao_png')
                    ->label('PNG')
                    ->icon('heroicon-o-photo')
                    ->url(fn (Member $record): string => route('member.card.png', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('numero');
    }
}
