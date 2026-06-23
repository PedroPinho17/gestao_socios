<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->assertAssignablePermissao($data['permissao_id'] ?? null);

        $data['must_change_password'] = true;

        return $data;
    }

    private function assertAssignablePermissao(mixed $permissaoId): void
    {
        $allowed = auth()->user()?->assignablePermissaoIds() ?? [];

        if (! in_array((int) $permissaoId, $allowed, true)) {
            abort(403);
        }
    }
}
