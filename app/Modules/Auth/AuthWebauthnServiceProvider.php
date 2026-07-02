<?php

namespace App\Modules\Auth;

use App\Modules\Auth\Http\Middleware\EnsurePasskeysEnabled;
use App\Modules\Auth\Http\Responses\StaffWebauthnLoginSuccessResponse;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\WebauthnCredentialService;
use App\Support\WebauthnSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\ServiceProvider;
use LaravelWebauthn\Contracts\LoginSuccessResponse as LoginSuccessResponseContract;
use LaravelWebauthn\Services\Webauthn;

class AuthWebauthnServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginSuccessResponseContract::class, StaffWebauthnLoginSuccessResponse::class);
    }

    public function boot(): void
    {
        if (! config('webauthn.enable', true)) {
            return;
        }

        RateLimiter::for('webauthn-login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perMinute(10)->by($email !== '' ? $email : $request->ip()),
            ];
        });

        Webauthn::authenticateUsing(function (Request $request): ?User {
            if (! $request->is('webauthn/auth') || ! WebauthnSettings::enabled()) {
                return null;
            }

            return app(WebauthnCredentialService::class)->assertStaffLogin($request);
        });

        $this->app->booted(function (): void {
            foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
                if (! $route instanceof Route) {
                    continue;
                }

                if (str_starts_with($route->uri(), 'webauthn/')) {
                    $route->middleware(EnsurePasskeysEnabled::class);
                }
            }
        });
    }
}
