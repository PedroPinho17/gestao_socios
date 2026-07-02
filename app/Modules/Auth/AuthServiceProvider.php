<?php

namespace App\Modules\Auth;

use App\Modules\Auth\Filament\Pages\ManagePasskeys;
use App\Modules\Auth\Filament\Resources\Users\UserResource;
use App\Modules\Core\ModuleServiceProvider;
use Filament\Pages\Page;

class AuthServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        $this->app->register(AuthWebauthnServiceProvider::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    public static function filamentResources(): array
    {
        return [
            UserResource::class,
        ];
    }

    /**
     * @return list<class-string<Page>>
     */
    public static function filamentPages(): array
    {
        return [
            ManagePasskeys::class,
        ];
    }
}
