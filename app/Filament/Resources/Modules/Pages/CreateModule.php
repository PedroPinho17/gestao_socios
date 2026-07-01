<?php

namespace App\Filament\Resources\Modules\Pages;

use App\Filament\Resources\Modules\ModuleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateModule extends CreateRecord
{
    protected static string $resource = ModuleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = Str::slug((string) ($data['slug'] ?? ''), '_');
        $data['is_core'] = false;

        if (! isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        return $data;
    }
}
