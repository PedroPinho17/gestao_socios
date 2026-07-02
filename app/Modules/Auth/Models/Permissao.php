<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permissao extends Model
{
    public const IMPERADOR = 1;

    public const ADMINISTRADOR = 2;

    public const TESOUREIRO = 3;

    protected $table = 'permissoes';

    protected $fillable = [
        'permissao',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isImperador(): bool
    {
        return $this->id === self::IMPERADOR;
    }

    public function isAdministrador(): bool
    {
        return $this->id === self::ADMINISTRADOR;
    }

    public function isTesoureiro(): bool
    {
        return $this->id === self::TESOUREIRO;
    }

    public function canManageClub(): bool
    {
        return in_array($this->id, [self::IMPERADOR, self::ADMINISTRADOR], true);
    }
}
