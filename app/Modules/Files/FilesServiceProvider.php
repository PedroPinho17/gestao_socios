<?php

namespace App\Modules\Files;

use App\Modules\Core\ModuleServiceProvider;

class FilesServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
