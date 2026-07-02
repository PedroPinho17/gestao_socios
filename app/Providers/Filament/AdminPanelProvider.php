<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Models\AppSetting;
use App\Models\ClubSetting;
use App\Modules\Auth\Filament\Pages\Login;
use App\Modules\Auth\Filament\Pages\ManagePasskeys;
use App\Modules\Core\Filament\FilamentRegistrar;
use App\Support\ClubBranding;
use App\Support\WebauthnSettings;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile()
            ->multiFactorAuthentication(
                providers: [AppAuthentication::make()],
                isRequired: fn (): bool => once(fn (): bool => AppSetting::bool(AppSetting::MFA_OBRIGATORIO)),
            )
            ->brandName(fn (): string => once(fn (): string => ClubSetting::current()->nome_clube))
            ->brandLogo(fn (): Htmlable|string|null => once(fn (): Htmlable|string|null => ClubBranding::brandLockupHtml() ?? ClubBranding::logoUrl()))
            ->brandLogoHeight('auto')
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => view('partials.favicon')->render()
                    .view('partials.pwa-prompt-capture')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('partials.pwa-install')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => view('partials.pwa-scripts')->render(),
            )
            ->userMenuItems([
                MenuItem::make()
                    ->label('Passkeys')
                    ->icon(Heroicon::OutlinedFingerPrint)
                    ->url(fn (): string => ManagePasskeys::getUrl())
                    ->visible(fn (): bool => WebauthnSettings::enabled()),
            ])
            ->spa()
            ->spaUrlExceptions([
                '/cartao/*',
                '/relatorios/*',
            ])
            ->colors([
                'primary' => Color::hex(
                    once(fn (): string => ClubSetting::current()->panel_primary_color ?? '#10b981'),
                ),
            ])
            ->resources(FilamentRegistrar::resources())
            ->pages(FilamentRegistrar::pages())
            ->widgets(FilamentRegistrar::widgets())
            ->middleware($this->panelMiddleware())
            ->authMiddleware([
                Authenticate::class,
                EnsurePasswordIsChanged::class,
            ]);
    }

    /**
     * @return list<class-string>
     */
    private function panelMiddleware(): array
    {
        $middleware = [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ];

        if (! app()->environment('local')) {
            array_splice($middleware, 3, 0, [AuthenticateSession::class]);
        }

        return $middleware;
    }
}
