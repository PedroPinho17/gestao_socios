<?php

namespace App\Modules\Members\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periodicidade extends Model
{
    protected $fillable = [
        'slug',
        'nome',
        'meses',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'meses' => 'integer',
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
