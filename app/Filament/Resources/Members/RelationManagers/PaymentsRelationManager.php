<?php

namespace App\Filament\Resources\Members\RelationManagers;

use App\Mail\PaymentReceiptMail;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                    })
                    ->after(function (Payment $record): void {
                        static::sendReceiptEmail($record, automatic: true);
                    }),
            ])
            ->recordActions([
                Action::make('comprovativo')
                    ->label('Comprovativo')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn ($record): string => route('payments.receipt.pdf', $record))
                    ->openUrlInNewTab(),
                Action::make('enviar_email')
                    ->label('Enviar por email')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Enviar comprovativo por email')
                    ->modalDescription(fn (Payment $record): string => $record->member?->email
                        ? 'Enviar o comprovativo para '.$record->member->email.'?'
                        : 'Este sócio não tem email definido na ficha.')
                    ->action(function (Payment $record): void {
                        static::sendReceiptEmail($record, automatic: false);
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('data', 'desc');
    }

    protected static function sendReceiptEmail(Payment $payment, bool $automatic): void
    {
        $member = $payment->member;
        $email = $member?->email;

        if (blank($email)) {
            Notification::make()
                ->title('Comprovativo não enviado')
                ->body($automatic
                    ? 'O pagamento foi registado, mas o sócio não tem email na ficha.'
                    : 'O sócio não tem email definido na ficha.')
                ->warning()
                ->send();

            return;
        }

        try {
            Mail::to($email)->send(new PaymentReceiptMail($payment));

            Notification::make()
                ->title('Comprovativo enviado')
                ->body('Enviado para '.$email.'.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar comprovativo de pagamento', [
                'payment_id' => $payment->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Não foi possível enviar o comprovativo')
                ->body($automatic
                    ? 'O pagamento foi registado, mas o email falhou. Pode reenviar com a ação «Enviar por email».'
                    : 'O envio do email falhou. Verifique a configuração de email.')
                ->danger()
                ->send();
        }
    }
}
