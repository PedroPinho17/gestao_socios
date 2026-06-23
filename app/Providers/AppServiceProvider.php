<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\Payment;
use App\Services\QuotaService;
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
