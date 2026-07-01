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
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => FeatureRegistry::clearCache());
        static::deleted(fn () => FeatureRegistry::clearCache());
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
