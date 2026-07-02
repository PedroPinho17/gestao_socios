<?php

namespace App\Modules\Auth\Filament\Pages;

use App\Modules\Auth\Services\WebauthnCredentialService;
use App\Support\WebauthnSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use LaravelWebauthn\Models\WebauthnKey;
use UnitEnum;

class ManagePasskeys extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    protected static ?string $navigationLabel = 'Passkeys';

    protected static ?string $title = 'Chaves de acesso (passkey)';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.auth.manage-passkeys';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->check() && WebauthnSettings::enabled();
    }

    public function getTitle(): string|Htmlable
    {
        return 'Chaves de acesso (passkey)';
    }

    /**
     * @return list<WebauthnKey>
     */
    public function getPasskeys(): array
    {
        $user = auth()->user();

        if ($user === null) {
            return [];
        }

        return $user->webauthnKeys()->orderByDesc('created_at')->get()->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registerPasskey')
                ->label('Registar passkey')
                ->icon(Heroicon::OutlinedPlus)
                ->form([
                    TextInput::make('name')
                        ->label('Nome deste dispositivo')
                        ->placeholder('Ex.: Portátil Pedro')
                        ->required()
                        ->maxLength(120),
                ])
                ->action(function (array $data): void {
                    $this->dispatch('register-passkey', name: $data['name']);
                }),
        ];
    }

    public function deletePasskey(int $keyId): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        WebauthnKey::query()
            ->whereKey($keyId)
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->delete();

        Notification::make()
            ->title('Passkey removida')
            ->success()
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    public function attestationOptions(): array
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        return app(WebauthnCredentialService::class)->attestationOptionsFor($user);
    }

    public function completeRegistration(string $name, array $credential): void
    {
        $user = auth()->user();
        abort_unless($user !== null, 403);

        app(WebauthnCredentialService::class)->registerKey($user, $credential, $name);

        Notification::make()
            ->title('Passkey registada')
            ->success()
            ->send();
    }
}
