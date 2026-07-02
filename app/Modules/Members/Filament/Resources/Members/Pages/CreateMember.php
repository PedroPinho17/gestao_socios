<?php

namespace App\Modules\Members\Filament\Resources\Members\Pages;

use App\Models\QuotaPlan;
use App\Modules\Members\Filament\Resources\Members\MemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['quota_plan_id'] ?? null)) {
            $data['quota_plan_id'] = QuotaPlan::query()->value('id');
        }

        return $data;
    }
}
