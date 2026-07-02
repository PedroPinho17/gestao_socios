<?php

namespace App\Modules\Settings\Filament\Resources\ModuleFeatures\Pages;

use App\Modules\Settings\Filament\Resources\ModuleFeatures\ModuleFeatureResource;
use App\Support\FeatureRegistry;
use Filament\Resources\Pages\CreateRecord;

class CreateModuleFeature extends CreateRecord
{
    protected static string $resource = ModuleFeatureResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $key = $data['key'] ?? '';
        $defaults = is_string($key) ? FeatureRegistry::catalogFormDefaults($key) : null;

        if ($defaults !== null) {
            $data = [...$defaults, ...$data];
            $data['is_system'] = true;
        } else {
            $data['is_system'] = false;
        }

        return $data;
    }
}
