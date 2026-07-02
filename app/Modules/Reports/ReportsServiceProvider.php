<?php

namespace App\Modules\Reports;

use App\Modules\Core\ModuleServiceProvider;

class ReportsServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
