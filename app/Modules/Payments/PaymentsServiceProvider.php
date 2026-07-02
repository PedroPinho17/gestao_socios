<?php

namespace App\Modules\Payments;

use App\Modules\Core\ModuleServiceProvider;

class PaymentsServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
