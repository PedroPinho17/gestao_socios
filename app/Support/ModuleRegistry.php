<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

final class ModuleRegistry
{
    public const SOCIOS = 'socios';

    public const CATALOGOS = 'catalogos';

    public const CARTOES = 'cartoes';

    public const COMUNICACOES = 'comunicacoes';

    public const RELATORIOS = 'relatorios';

    public const COMPROVATIVOS = 'comprovativos';

    public const AREA_SOCIO = 'area_socio';

    public const UTILIZADORES = 'utilizadores';

    public const AUDITORIA = 'auditoria';

    public const DEFINICOES = 'definicoes';

    public const LEMBRETES = 'lembretes';

    private const CACHE_KEY = 'modules.registry.v2';

    /**
     * Catálogo em código — ao adicionar funcionalidades novas, registe aqui e corra
     * `php artisan gestao:sync-modules` para criar o registo na base de dados.
     *
     * @return array<string, array{label: string, description: string, core?: bool, disabled_message?: string}>
     */
    public static function catalog(): array
    {
        return [
            self::SOCIOS => [
                'label' => 'Sócios e quotas',
                'description' => 'Fichas de sócios, planos de quota e pagamentos.',
                'core' => true,
            ],
            self::CATALOGOS => [
                'label' => 'Catálogos',
                'description' => 'Periodicidades e tipos de vencimento de quota.',
            ],
            self::CARTOES => [
                'label' => 'Cartões de sócio',
                'description' => 'Emissão, PDF/PNG, ZIP e layout do cartão.',
            ],
            self::COMUNICACOES => [
                'label' => 'Comunicações',
                'description' => 'Emails e links WhatsApp em massa aos sócios.',
            ],
            self::RELATORIOS => [
                'label' => 'Relatórios',
                'description' => 'Sócios em atraso, pagantes e exportações.',
            ],
            self::COMPROVATIVOS => [
                'label' => 'Comprovativos de pagamento',
                'description' => 'PDF e envio por email de comprovativos de quota.',
            ],
            self::AREA_SOCIO => [
                'label' => 'Área do sócio',
                'description' => 'Frontend React e contas de acesso dos sócios.',
                'disabled_message' => 'A área do sócio não está disponível neste clube. Contacte a secretaria se precisar de ajuda.',
            ],
            self::UTILIZADORES => [
                'label' => 'Utilizadores do painel',
                'description' => 'Gestão de contas staff (Imperador, Admin, Tesoureiro).',
            ],
            self::AUDITORIA => [
                'label' => 'Auditoria',
                'description' => 'Registo de actividade no backoffice.',
            ],
            self::DEFINICOES => [
                'label' => 'Definições do clube',
                'description' => 'Nome, cores, logótipo e personalização.',
            ],
            self::LEMBRETES => [
                'label' => 'Lembretes automáticos',
                'description' => 'Emails automáticos quando a quota está a vencer.',
            ],
        ];
    }

    /** @deprecated Use catalog() */
    public static function definitions(): array
    {
        return self::catalog();
    }

    public static function enabled(string $slug): bool
    {
        if (! self::tableExists()) {
            return self::catalogEnabledFallback($slug);
        }

        $module = self::allCached()->firstWhere('slug', $slug);

        if ($module instanceof Module) {
            return $module->isEnabled();
        }

        return false;
    }

    public static function disabledMessage(string $slug): string
    {
        if (self::tableExists()) {
            $module = self::allCached()->firstWhere('slug', $slug);

            if ($module instanceof Module && filled($module->disabled_message)) {
                return $module->disabled_message;
            }
        }

        $definition = self::catalog()[$slug] ?? null;

        if (is_array($definition) && filled($definition['disabled_message'] ?? null)) {
            return (string) $definition['disabled_message'];
        }

        return 'Esta funcionalidade não está disponível neste clube.';
    }

    /**
     * @return array<string, bool>
     */
    public static function activeMap(): array
    {
        if (! self::tableExists()) {
            return self::defaultActiveMap();
        }

        $map = [];

        foreach (self::allCached() as $module) {
            $map[$module->slug] = $module->isEnabled();
        }

        foreach (self::catalog() as $slug => $definition) {
            if (! array_key_exists($slug, $map)) {
                $map[$slug] = ! ($definition['core'] ?? false) || true;
            }
        }

        return $map;
    }

    /**
     * @return array<string, bool>
     */
    public static function defaultActiveMap(): array
    {
        return array_map(
            fn (): bool => true,
            array_fill_keys(array_keys(self::catalog()), true),
        );
    }

    /**
     * @return Collection<int, Module>
     */
    public static function allCached(): Collection
    {
        if (! self::tableExists()) {
            return collect();
        }

        $rows = Cache::get(self::CACHE_KEY);

        if (! is_array($rows)) {
            $rows = self::refreshCache();
        }

        return collect($rows)->map(function (array $row): Module {
            $module = new Module;
            $module->forceFill($row);
            $module->exists = true;

            return $module;
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function refreshCache(): array
    {
        $rows = Module::query()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (Module $module): array => $module->only([
                'id',
                'slug',
                'label',
                'description',
                'disabled_message',
                'enabled',
                'is_core',
                'sort_order',
            ]))
            ->values()
            ->all();

        Cache::forever(self::CACHE_KEY, $rows);

        return $rows;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('modules.registry');
    }

    /**
     * Importa módulos novos do catálogo em código para a base de dados.
     * Não altera enabled/is_core de registos existentes (excepto metadados por omissão no 1.º sync).
     */
    public static function syncCatalog(): int
    {
        if (! self::tableExists()) {
            return 0;
        }

        $created = 0;
        $sort = (int) Module::query()->max('sort_order');
        $legacy = AppSetting::json(AppSetting::MODULOS_ATIVOS);

        foreach (self::catalog() as $slug => $definition) {
            $existing = Module::query()->where('slug', $slug)->first();

            if ($existing) {
                continue;
            }

            $enabled = true;

            if (is_array($legacy) && array_key_exists($slug, $legacy)) {
                $enabled = filter_var($legacy[$slug], FILTER_VALIDATE_BOOLEAN);
            }

            if ($definition['core'] ?? false) {
                $enabled = true;
            }

            Module::query()->create([
                'slug' => $slug,
                'label' => $definition['label'],
                'description' => $definition['description'] ?? null,
                'disabled_message' => $definition['disabled_message'] ?? null,
                'enabled' => $enabled,
                'is_core' => (bool) ($definition['core'] ?? false),
                'sort_order' => $sort += 10,
            ]);

            $created++;
        }

        self::clearCache();

        return $created;
    }

    private static function catalogEnabledFallback(string $slug): bool
    {
        $definition = self::catalog()[$slug] ?? null;

        if ($definition === null) {
            return false;
        }

        if ($definition['core'] ?? false) {
            return true;
        }

        $legacy = AppSetting::json(AppSetting::MODULOS_ATIVOS);

        if (is_array($legacy) && array_key_exists($slug, $legacy)) {
            return filter_var($legacy[$slug], FILTER_VALIDATE_BOOLEAN);
        }

        return true;
    }

    private static function tableExists(): bool
    {
        try {
            return Schema::hasTable('modules');
        } catch (\Throwable) {
            return false;
        }
    }
}
