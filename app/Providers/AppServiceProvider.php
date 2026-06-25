<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\Payment;
use App\Services\QuotaService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        JsonResource::withoutWrapping();

        if (app()->environment('local')) {
            config(['auth.timebox_duration' => 0]);
        }

        if (str_starts_with((string) config('app.url'), 'https://')) {
            config(['session.secure' => true]);
        }

        foreach ([Member::class, Payment::class] as $model) {
            $model::saved(fn () => QuotaService::clearSituationCache());
            $model::deleted(fn () => QuotaService::clearSituationCache());
        }

        Password::defaults(fn () => Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols());
    }
}
