<?php

namespace App\Modules\Auth\Filament\Resources\Users\Pages;

use App\Modules\Auth\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => UserResource::canDelete($this->record)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assertAssignablePermissao($data['permissao_id'] ?? $this->record->permissao_id);

        if (filled($data['password'] ?? null)) {
            $data['must_change_password'] = true;
            $data['password_changed_at'] = null;
        } else {
            unset($data['password']);
        }

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
