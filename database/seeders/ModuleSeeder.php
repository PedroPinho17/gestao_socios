<?php

namespace Database\Seeders;

use App\Support\FeatureRegistry;
use App\Support\ModuleRegistry;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        ModuleRegistry::syncCatalog();
        FeatureRegistry::syncCatalog();
    }
}
