<?php

namespace App\Models;

use App\Support\FeatureRegistry;
use App\Support\ModuleRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'slug',
        'label',
        'description',
        'disabled_message',
        'enabled',
        'is_core',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_core' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            ModuleRegistry::clearCache();
            FeatureRegistry::clearCache();
        });

        static::deleted(function (): void {
            ModuleRegistry::clearCache();
            FeatureRegistry::clearCache();
        });
    }

    /**
     * @return HasMany<ModuleFeature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(ModuleFeature::class)->orderBy('sort_order');
    }

    public function isEnabled(): bool
    {
        if ($this->is_core) {
            return true;
        }

        return $this->enabled;
    }

    /**
     * @return array<int, string>
     */
    public static function selectOptions(): array
    {
        return static::query()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->mapWithKeys(fn (self $module): array => [
                $module->id => "{$module->label} ({$module->slug})",
            ])
            ->all();
    }
}
