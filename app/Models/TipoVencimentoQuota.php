<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoVencimentoQuota extends Model
{
    protected $table = 'tipos_vencimento_quota';

    protected $fillable = [
        'slug',
        'nome',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
        ];
    }

    public function quotaPlans(): HasMany
    {
        return $this->hasMany(QuotaPlan::class);
    }

    public function isSlug(string $slug): bool
    {
        return $this->slug === $slug;
    }

    /**
     * @return array<int, string>
     */
    public static function optionsForSelect(): array
    {
        return static::query()
            ->orderBy('ordem')
            ->pluck('nome', 'id')
            ->all();
    }
}
