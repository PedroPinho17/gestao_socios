<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    public const MFA_OBRIGATORIO = 'mfa_obrigatorio';

    public const DIAS_ALERTA_QUOTA = 'dias_alerta_quota';

    public const LEMBRETES_AUTOMATICOS = 'lembretes_automaticos';

    public const PASSKEYS_ATIVAS = 'passkeys_ativas';

    public const MODULOS_ATIVOS = 'modulos_ativos';

    protected $fillable = [
        'chave',
        'valor',
    ];

    /** @var array<string, mixed> */
    private static array $memory = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$memory)) {
            return static::$memory[$key];
        }

        if (! static::tableExists()) {
            return static::$memory[$key] = $default;
        }

        $value = Cache::rememberForever(static::cacheKey($key), function () use ($key, $default) {
            return static::query()->where('chave', $key)->value('valor') ?? $default;
        });

        return static::$memory[$key] = $value ?? $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(static::get($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) static::get($key, $default);
    }

    public static function json(string $key, mixed $default = null): mixed
    {
        $raw = static::get($key);

        if ($raw === null || $raw === '') {
            return $default;
        }

        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode((string) $raw, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    }

    public static function setJson(string $key, mixed $value): void
    {
        static::set($key, json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['chave' => $key],
            ['valor' => is_bool($value) ? ($value ? '1' : '0') : (string) $value],
        );

        Cache::forget(static::cacheKey($key));
        unset(static::$memory[$key]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::set($key, $value);
        }
    }

    private static function cacheKey(string $key): string
    {
        return "app_setting.{$key}";
    }

    private static function tableExists(): bool
    {
        try {
            return Schema::hasTable('app_settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
