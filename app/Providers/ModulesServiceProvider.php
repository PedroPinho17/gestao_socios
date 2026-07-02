<?php

namespace App\Providers;

use App\Modules\Core\ModuleCatalog;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach (ModuleCatalog::providers() as $provider) {
            $this->app->register($provider);
        }
    }
}
