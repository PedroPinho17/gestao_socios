<?php

namespace App\Modules\Members\Filament\Resources\Members\Actions;

use App\Modules\Members\Models\Member;
use App\Modules\Members\Services\MemberAccountService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CreateMemberAccountAction
{
    public static function make(): Action
    {
        return Action::make('criarContaAcesso')
            ->label(fn (Member $record): string => $record->user
                ? 'Atualizar conta de acesso'
                : 'Criar conta de acesso')
            ->icon('heroicon-o-key')
            ->color(fn (Member $record): string => $record->user ? 'gray' : 'primary')
            ->modalHeading(fn (Member $record): string => $record->user
                ? 'Atualizar conta de acesso'
                : 'Criar conta de acesso')
            ->modalDescription('O sócio usa este email e password para entrar na área do sócio (frontend).')
            ->modalSubmitActionLabel(fn (Member $record): string => $record->user ? 'Guardar' : 'Criar conta')
            ->schema([
                TextInput::make('email')
                    ->label('Email de login')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('Nome no login')
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->rule(Password::defaults())
                    ->required(fn (Member $record): bool => ! $record->user)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->confirmed()
                    ->helperText('Mínimo 12 caracteres, com maiúsculas, minúsculas, números e símbolos. Deixe em branco para manter a password actual.'),
                TextInput::make('password_confirmation')
                    ->label('Confirmar password')
                    ->password()
                    ->revealable()
                    ->required(fn (Member $record, Get $get): bool => ! $record->user || filled($get('password')))
                    ->dehydrated(false),
            ])
            ->fillForm(fn (Member $record): array => [
                'email' => $record->user?->email ?? $record->email ?? '',
                'name' => $record->user?->name ?? $record->nome,
            ])
            ->action(function (Member $record, array $data): void {
                $record->loadMissing('user');
                $hadAccount = $record->user !== null;

                try {
                    $user = app(MemberAccountService::class)->createOrUpdate(
                        $record,
                        $data['email'],
                        filled($data['password'] ?? null) ? $data['password'] : null,
                        filled($data['name'] ?? null) ? $data['name'] : null,
                    );
                } catch (ValidationException $exception) {
                    Notification::make()
                        ->title('Não foi possível guardar a conta de acesso')
                        ->body(collect($exception->errors())->flatten()->implode(' '))
                        ->danger()
                        ->send();

                    throw $exception;
                }

                $record->unsetRelation('user');
                $record->load('user');

                Notification::make()
                    ->title($hadAccount ? 'Conta de acesso actualizada' : 'Conta de acesso criada')
                    ->body("Login: {$user->email}")
                    ->success()
                    ->send();
            });
    }
}
