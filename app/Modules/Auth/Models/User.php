<?php

namespace App\Modules\Auth\Models;

use App\Modules\Members\Models\Member;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use LaravelWebauthn\WebauthnAuthenticatable;

// Sócio = User com member_id preenchido e permissao_id null (sem papel de staff no Filament).
#[Fillable(['name', 'email', 'password', 'permissao_id', 'member_id', 'must_change_password', 'password_changed_at'])]
#[Hidden(['password', 'remember_token', 'app_authentication_secret', 'app_authentication_recovery_codes'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, WebauthnAuthenticatable;

    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }

    public function permissao(): BelongsTo
    {
        return $this->belongsTo(Permissao::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isMember(): bool
    {
        return filled($this->member_id);
    }

    public function isStaff(): bool
    {
        return ! $this->isMember();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->isMember()) {
            return false;
        }

        return in_array($this->permissao_id, [
            Permissao::IMPERADOR,
            Permissao::ADMINISTRADOR,
            Permissao::TESOUREIRO,
        ], true);
    }

    public function isImperador(): bool
    {
        return $this->permissao_id === Permissao::IMPERADOR;
    }

    public function isAdmin(): bool
    {
        return $this->permissao_id === Permissao::ADMINISTRADOR;
    }

    public function isTreasurer(): bool
    {
        return $this->permissao_id === Permissao::TESOUREIRO;
    }

    public function canManageClub(): bool
    {
        return in_array($this->permissao_id, [Permissao::IMPERADOR, Permissao::ADMINISTRADOR], true);
    }

    public function canManageUsers(): bool
    {
        return $this->isImperador() || $this->isAdmin();
    }

    public function canViewAudit(): bool
    {
        return $this->isImperador();
    }

    /**
     * @return list<int>
     */
    public function assignablePermissaoIds(): array
    {
        if ($this->isImperador()) {
            return [
                Permissao::IMPERADOR,
                Permissao::ADMINISTRADOR,
                Permissao::TESOUREIRO,
            ];
        }

        if ($this->isAdmin()) {
            return [
                Permissao::ADMINISTRADOR,
                Permissao::TESOUREIRO,
            ];
        }

        return [];
    }

    public function canManageUser(User $target): bool
    {
        if (! $this->canManageUsers()) {
            return false;
        }

        if ($this->getKey() === $target->getKey()) {
            return true;
        }

        if ($this->isImperador()) {
            return true;
        }

        return in_array($target->permissao_id, $this->assignablePermissaoIds(), true);
    }
}
