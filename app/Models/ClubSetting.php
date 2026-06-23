<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class ClubSetting extends Model
{
    use LogsActivity;

    private const CACHE_KEY = 'club_settings.id';

    private static ?self $currentCache = null;

    protected $fillable = [
        'nome_clube',
        'logo_path',
        'card_gradient_from',
        'card_gradient_to',
        'card_accent_color',
        'card_titulo',
        'card_campo_extra_label',
        'show_proximo_vencimento',
        'show_cargo',
        'show_email',
        'show_telefone',
    ];

    protected function casts(): array
    {
        return [
            'show_proximo_vencimento' => 'boolean',
            'show_cargo' => 'boolean',
            'show_email' => 'boolean',
            'show_telefone' => 'boolean',
        ];
    }

    public static function current(): self
    {
        if (static::$currentCache instanceof self) {
            return static::$currentCache;
        }

        $id = Cache::rememberForever(self::CACHE_KEY, function (): int {
            return (int) static::query()->firstOrCreate(
                ['id' => 1],
                static::defaultAttributes(),
            )->id;
        });

        return static::$currentCache = static::query()->findOrFail($id);
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget(self::CACHE_KEY);
            static::$currentCache = null;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private static function defaultAttributes(): array
    {
        return [
            'nome_clube' => 'O meu clube',
            'card_gradient_from' => '#0f766e',
            'card_gradient_to' => '#0f172a',
            'card_accent_color' => '#d1fae5',
            'card_titulo' => 'Sócio',
            'card_campo_extra_label' => 'Cargo',
            'show_proximo_vencimento' => true,
            'show_cargo' => true,
            'show_email' => false,
            'show_telefone' => false,
        ];
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return route('secure.club.logo');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nome_clube', 'logo_path', 'card_gradient_from', 'card_gradient_to',
                'card_accent_color', 'card_titulo', 'card_campo_extra_label',
                'show_proximo_vencimento', 'show_cargo', 'show_email', 'show_telefone',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
