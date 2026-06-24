<?php

namespace App\Providers\Filament;

use App\Filament\Clusters\CatalogosCluster;
use App\Filament\Pages\ChangeRequiredPassword;
use App\Filament\Pages\ClubSettingsPage;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\SystemSettingsPage;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Periodicidades\PeriodicidadeResource;
use App\Filament\Resources\QuotaPlans\QuotaPlanResource;
use App\Filament\Resources\TiposVencimentoQuota\TipoVencimentoQuotaResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\ClubStatsWidget;
use App\Filament\Widgets\QuotaAlertsWidget;
use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Models\AppSetting;
use App\Models\ClubSetting;
use App\Support\ClubBranding;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ->login()
            ->profile()
            ->multiFactorAuthentication(
                providers: [AppAuthentication::make()],
                isRequired: fn (): bool => once(fn (): bool => AppSetting::bool(AppSetting::MFA_OBRIGATORIO)),
            )
            ->brandName(fn (): string => once(fn (): string => ClubSetting::current()->nome_clube))
            ->brandLogo(fn (): Htmlable|string|null => once(fn (): Htmlable|string|null => ClubBranding::brandLockupHtml() ?? ClubBranding::logoUrl()))
            ->brandLogoHeight('auto')
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
            ->resources([
                MemberResource::class,
                QuotaPlanResource::class,
                PeriodicidadeResource::class,
                TipoVencimentoQuotaResource::class,
                UserResource::class,
                ActivityLogResource::class,
            ])
            ->pages([
                Dashboard::class,
                CatalogosCluster::class,
                ClubSettingsPage::class,
                SystemSettingsPage::class,
                ChangeRequiredPassword::class,
            ])
            ->widgets([
                ClubStatsWidget::class,
                QuotaAlertsWidget::class,
            ])
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
