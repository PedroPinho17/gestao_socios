<?php

namespace App\Modules\Auth\Filament\Pages;

use App\Support\WebauthnSettings;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $email = strtolower((string) ($this->form->getState()['email'] ?? request()->ip()));
        $key = 'admin-login:'.$email.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            Notification::make()
                ->title('Demasiadas tentativas')
                ->body("Aguarde {$seconds} segundos antes de tentar novamente.")
                ->danger()
                ->send();

            return null;
        }

        RateLimiter::hit($key, 60);

        $response = parent::authenticate();

        if ($response !== null) {
            RateLimiter::clear($key);
        }

        return $response;
    }

    public function getFormContentComponent(): Component
    {
        $footer = [
            Actions::make($this->getFormActions())
                ->alignment($this->getFormActionsAlignment())
                ->fullWidth($this->hasFullWidthFormActions())
                ->key('form-actions'),
        ];

        if (WebauthnSettings::enabled()) {
            $footer[] = new HtmlString(view('filament.auth.passkey-login-divider')->render());
            $footer[] = Actions::make([$this->getPasskeyLoginAction()])
                ->fullWidth(true)
                ->key('passkey-form-actions');
            $footer[] = new HtmlString(view('filament.auth.passkey-login-script')->render());
        }

        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer($footer)
            ->visible(fn (): bool => blank($this->userUndertakingMultiFactorAuthentication));
    }

    protected function getPasskeyLoginAction(): Action
    {
        return Action::make('passkeyLogin')
            ->label('Entrar com passkey')
            ->icon(Heroicon::OutlinedFingerPrint)
            ->color('gray')
            ->outlined()
            ->extraAttributes([
                'id' => 'staff-passkey-login',
                'type' => 'button',
            ])
            ->livewireClickHandlerEnabled(false)
            ->action(static fn () => null);
    }
}
