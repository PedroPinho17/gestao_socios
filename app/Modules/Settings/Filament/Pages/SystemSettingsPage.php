<?php

namespace App\Modules\Settings\Filament\Pages;

use App\Models\AppSetting;
use App\Services\QuotaService;
use App\Support\FeatureRegistry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class SystemSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Sistema';

    protected static ?string $title = 'Definições do sistema';

    protected static string|UnitEnum|null $navigationGroup = 'Configuração';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.system-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isImperador() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'mfa_obrigatorio' => AppSetting::bool(AppSetting::MFA_OBRIGATORIO),
            'dias_alerta_quota' => AppSetting::int(AppSetting::DIAS_ALERTA_QUOTA, 7),
            'lembretes_automaticos' => AppSetting::bool(AppSetting::LEMBRETES_AUTOMATICOS),
            'passkeys_ativas' => AppSetting::bool(AppSetting::PASSKEYS_ATIVAS, true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Segurança')
                    ->description('Estas opções aplicam-se a todos os utilizadores do painel.')
                    ->schema([
                        Toggle::make('mfa_obrigatorio')
                            ->label('Autenticação de dois factores obrigatória')
                            ->helperText('Quando activa, todos devem configurar 2FA no perfil antes de usar o painel.'),
                        Toggle::make('passkeys_ativas')
                            ->label('Passkeys (WebAuthn)')
                            ->helperText('Permite login e gestão de chaves de acesso no backoffice e na área do sócio. O .env pode desactivar globalmente com WEBAUTHN_ENABLE=false.')
                            ->default(true),
                    ]),
                Section::make('Quotas')
                    ->schema([
                        TextInput::make('dias_alerta_quota')
                            ->label('Dias de aviso antes do vencimento')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(90)
                            ->required()
                            ->suffix('dias')
                            ->helperText('Sócios dentro deste prazo aparecem como «vence em breve» no painel e filtros.'),
                        Toggle::make('lembretes_automaticos')
                            ->label('Lembretes automáticos de quota por email')
                            ->visible(fn (): bool => FeatureRegistry::enabled('command.quota_reminders'))
                            ->helperText('Quando activo, envia automaticamente um email ao sócio quando a quota está dentro do prazo de aviso acima e ainda não foi paga (uma vez por vencimento). Requer o agendador (cron) configurado.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('system-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Guardar')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::setMany([
            AppSetting::MFA_OBRIGATORIO => (bool) ($data['mfa_obrigatorio'] ?? false),
            AppSetting::DIAS_ALERTA_QUOTA => (int) ($data['dias_alerta_quota'] ?? 7),
            AppSetting::LEMBRETES_AUTOMATICOS => (bool) ($data['lembretes_automaticos'] ?? false),
            AppSetting::PASSKEYS_ATIVAS => (bool) ($data['passkeys_ativas'] ?? false),
        ]);

        QuotaService::clearSituationCache();

        Notification::make()
            ->title('Definições do sistema guardadas')
            ->success()
            ->send();
    }
}
