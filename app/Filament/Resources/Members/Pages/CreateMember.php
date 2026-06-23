<?php

namespace App\Filament\Resources\Members\Pages;

use App\Filament\Resources\Members\MemberResource;
use App\Models\QuotaPlan;
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
