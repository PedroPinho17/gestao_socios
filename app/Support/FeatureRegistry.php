<?php

namespace App\Support;

use App\Filament\Clusters\CatalogosCluster;
use App\Filament\Pages\ClubSettingsPage;
use App\Filament\Pages\CommunicationsPage;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Periodicidades\PeriodicidadeResource;
use App\Filament\Resources\QuotaPlans\QuotaPlanResource;
use App\Filament\Resources\TiposVencimentoQuota\TipoVencimentoQuotaResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Module;
use App\Models\ModuleFeature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

final class FeatureRegistry
{
    private const CACHE_KEY = 'module_features.registry.v1';

    /**
     * Catálogo em código — registe novas páginas/rotas aqui e corra gestao:sync-modules.
     *
     * @return array<string, array{
     *     label: string,
     *     module: string,
     *     description?: string,
     *     binding_type?: string,
     *     binding_target?: string,
     * }>
     */
    public static function catalog(): array
    {
        return [
            'filament.members' => [
                'label' => 'Sócios',
                'module' => ModuleRegistry::SOCIOS,
                'binding_type' => 'filament_resource',
                'binding_target' => MemberResource::class,
            ],
            'filament.quota_plans' => [
                'label' => 'Planos de quota',
                'module' => ModuleRegistry::SOCIOS,
                'binding_type' => 'filament_resource',
                'binding_target' => QuotaPlanResource::class,
            ],
            'filament.catalogos' => [
                'label' => 'Catálogos (grupo)',
                'module' => ModuleRegistry::CATALOGOS,
                'binding_type' => 'filament_cluster',
                'binding_target' => CatalogosCluster::class,
            ],
            'filament.periodicidades' => [
                'label' => 'Periodicidades',
                'module' => ModuleRegistry::CATALOGOS,
                'binding_type' => 'filament_resource',
                'binding_target' => PeriodicidadeResource::class,
            ],
            'filament.tipos_vencimento' => [
                'label' => 'Tipos de vencimento',
                'module' => ModuleRegistry::CATALOGOS,
                'binding_type' => 'filament_resource',
                'binding_target' => TipoVencimentoQuotaResource::class,
            ],
            'filament.communications' => [
                'label' => 'Comunicações',
                'module' => ModuleRegistry::COMUNICACOES,
                'binding_type' => 'filament_page',
                'binding_target' => CommunicationsPage::class,
            ],
            'filament.club_settings' => [
                'label' => 'Definições do clube',
                'module' => ModuleRegistry::DEFINICOES,
                'binding_type' => 'filament_page',
                'binding_target' => ClubSettingsPage::class,
            ],
            'filament.users' => [
                'label' => 'Utilizadores do painel',
                'module' => ModuleRegistry::UTILIZADORES,
                'binding_type' => 'filament_resource',
                'binding_target' => UserResource::class,
            ],
            'filament.audit' => [
                'label' => 'Auditoria',
                'module' => ModuleRegistry::AUDITORIA,
                'binding_type' => 'filament_resource',
                'binding_target' => ActivityLogResource::class,
            ],
            'filament.cards' => [
                'label' => 'Cartões de sócio (exportação)',
                'module' => ModuleRegistry::CARTOES,
                'description' => 'Acções de cartão nas fichas de sócios e exportação ZIP/PDF/PNG.',
                'binding_type' => 'route_group',
                'binding_target' => 'member.card',
            ],
            'filament.reports' => [
                'label' => 'Relatórios de sócios',
                'module' => ModuleRegistry::RELATORIOS,
                'binding_type' => 'route_group',
                'binding_target' => 'reports.',
            ],
            'filament.receipts' => [
                'label' => 'Comprovativos de pagamento',
                'module' => ModuleRegistry::COMPROVATIVOS,
                'binding_type' => 'route_group',
                'binding_target' => 'payments.receipt',
            ],
            'api.area_socio' => [
                'label' => 'Área do sócio (API / React)',
                'module' => ModuleRegistry::AREA_SOCIO,
                'binding_type' => 'api_group',
                'binding_target' => '/api/login',
            ],
            'command.quota_reminders' => [
                'label' => 'Lembretes automáticos (cron)',
                'module' => ModuleRegistry::LEMBRETES,
                'binding_type' => 'command',
                'binding_target' => 'gestao:send-quota-reminders',
            ],
        ];
    }

    /**
     * Funcionalidades do catálogo em código ainda não registadas na base de dados.
     *
     * @return array<string, string>
     */
    public static function missingCatalogSelectOptions(): array
    {
        $existingKeys = self::tableExists()
            ? ModuleFeature::query()->pluck('key')->all()
            : [];

        $moduleLabels = Module::query()->pluck('label', 'slug');

        $options = [];

        foreach (self::catalog() as $key => $definition) {
            if (in_array($key, $existingKeys, true)) {
                continue;
            }

            $moduleName = $moduleLabels[$definition['module']] ?? $definition['module'];
            $options[$key] = "{$definition['label']} ({$moduleName})";
        }

        asort($options);

        return $options;
    }

    public static function hasMissingCatalogEntries(): bool
    {
        return self::missingCatalogSelectOptions() !== [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function catalogFormDefaults(string $key): ?array
    {
        $definition = self::catalog()[$key] ?? null;

        if ($definition === null) {
            return null;
        }

        return [
            'module_id' => Module::query()->where('slug', $definition['module'])->value('id'),
            'label' => $definition['label'],
            'description' => $definition['description'] ?? null,
            'binding_type' => $definition['binding_type'] ?? null,
            'binding_target' => $definition['binding_target'] ?? null,
        ];
    }

    public static function enabled(string $key): bool
    {
        if (! self::tableExists()) {
            return self::catalogEnabledFallback($key);
        }

        $feature = self::allCached()->firstWhere('key', $key);

        if ($feature instanceof ModuleFeature) {
            return $feature->module?->isEnabled() ?? false;
        }

        $catalog = self::catalog()[$key] ?? null;

        if ($catalog === null) {
            return false;
        }

        return ModuleRegistry::enabled($catalog['module']);
    }

    public static function moduleSlugFor(string $key): ?string
    {
        if (self::tableExists()) {
            $feature = self::allCached()->firstWhere('key', $key);

            if ($feature instanceof ModuleFeature) {
                return $feature->module?->slug;
            }
        }

        return self::catalog()[$key]['module'] ?? null;
    }

    /**
     * @return Collection<int, ModuleFeature>
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

        $modules = ModuleRegistry::allCached()->keyBy('id');

        return collect($rows)->map(function (array $row) use ($modules): ModuleFeature {
            $feature = new ModuleFeature;
            $feature->forceFill($row);
            $feature->exists = true;

            if (isset($row['module_id'], $modules[$row['module_id']])) {
                $feature->setRelation('module', $modules[$row['module_id']]);
            }

            return $feature;
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function syncCatalog(): int
    {
        if (! self::tableExists() || ! Schema::hasTable('modules')) {
            return 0;
        }

        $created = 0;
        $sort = (int) ModuleFeature::query()->max('sort_order');

        foreach (self::catalog() as $key => $definition) {
            $module = Module::query()->where('slug', $definition['module'])->first();

            if (! $module) {
                continue;
            }

            $existing = ModuleFeature::query()->where('key', $key)->first();

            if ($existing) {
                continue;
            }

            ModuleFeature::query()->create([
                'module_id' => $module->id,
                'key' => $key,
                'label' => $definition['label'],
                'description' => $definition['description'] ?? null,
                'binding_type' => $definition['binding_type'] ?? null,
                'binding_target' => $definition['binding_target'] ?? null,
                'is_system' => true,
                'sort_order' => $sort += 10,
            ]);

            $created++;
        }

        self::clearCache();

        return $created;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function refreshCache(): array
    {
        $rows = ModuleFeature::query()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (ModuleFeature $feature): array => $feature->only([
                'id',
                'module_id',
                'key',
                'label',
                'description',
                'binding_type',
                'binding_target',
                'is_system',
                'sort_order',
            ]))
            ->values()
            ->all();

        Cache::forever(self::CACHE_KEY, $rows);

        return $rows;
    }

    private static function catalogEnabledFallback(string $key): bool
    {
        $catalog = self::catalog()[$key] ?? null;

        if ($catalog === null) {
            return false;
        }

        return ModuleRegistry::enabled($catalog['module']);
    }

    private static function tableExists(): bool
    {
        try {
            return Schema::hasTable('module_features');
        } catch (\Throwable) {
            return false;
        }
    }
}
