<?php

namespace App\Console\Commands;

use App\Support\FeatureRegistry;
use App\Support\ModuleRegistry;
use Illuminate\Console\Command;

class SyncModulesCommand extends Command
{
    protected $signature = 'gestao:sync-modules';

    protected $description = 'Sincroniza módulos e funcionalidades do catálogo em código para a base de dados.';

    public function handle(): int
    {
        $modules = ModuleRegistry::syncCatalog();
        $features = FeatureRegistry::syncCatalog();

        if ($modules === 0 && $features === 0) {
            $this->info('Catálogo já está actualizado.');

            return self::SUCCESS;
        }

        $this->info("Adicionados {$modules} módulo(s) e {$features} funcionalidade(s).");

        return self::SUCCESS;
    }
}
