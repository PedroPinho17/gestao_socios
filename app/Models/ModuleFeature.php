<?php

namespace App\Models;

use App\Support\FeatureRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleFeature extends Model
{
    protected $fillable = [
        'module_id',
        'key',
        'label',
        'description',
        'binding_type',
        'binding_target',
        'is_system',
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
            'is_system' => 'boolean',
            'enabled' => 'boolean',
            'is_core' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => FeatureRegistry::clearCache());
        static::deleted(fn () => FeatureRegistry::clearCache());
    }

    public function isEffectivelyEnabled(): bool
    {
        if (! $this->module?->isEnabled()) {
            return false;
        }

        if ($this->is_core) {
            return true;
        }

        return $this->enabled;
    }

    /**
     * @return array{label: string, color: string}
     */
    public function statusBadge(): array
    {
        if (! $this->module?->isEnabled()) {
            return ['label' => 'Módulo desactivado', 'color' => 'gray'];
        }

        if ($this->is_core) {
            return ['label' => 'Core', 'color' => 'info'];
        }

        if (! $this->enabled) {
            return ['label' => 'Desactivada', 'color' => 'warning'];
        }

        return ['label' => 'Activada', 'color' => 'success'];
    }

    public function canToggleEnabled(): bool
    {
        return ! $this->is_core && $this->module?->isEnabled();
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
