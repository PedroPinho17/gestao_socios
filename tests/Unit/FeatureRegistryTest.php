<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\ModuleFeature;
use App\Support\FeatureRegistry;
use App\Support\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_disabled_when_parent_module_off(): void
    {
        $module = $this->catalogModule(ModuleRegistry::CATALOGOS, enabled: false);
        $this->createFeature($module, 'filament.periodicidades', enabled: true);

        FeatureRegistry::clearCache();
        ModuleRegistry::clearCache();

        $this->assertFalse(FeatureRegistry::enabled('filament.periodicidades'));
    }

    public function test_feature_respects_own_toggle_when_module_on(): void
    {
        $module = $this->catalogModule(ModuleRegistry::CATALOGOS, enabled: true);
        $this->createFeature($module, 'filament.periodicidades', enabled: false);
        $this->createFeature($module, 'filament.tipos_vencimento', enabled: true);

        FeatureRegistry::clearCache();
        ModuleRegistry::clearCache();

        $this->assertFalse(FeatureRegistry::enabled('filament.periodicidades'));
        $this->assertTrue(FeatureRegistry::enabled('filament.tipos_vencimento'));
    }

    public function test_core_feature_stays_on_when_module_on_even_if_disabled_flag(): void
    {
        $module = $this->catalogModule(ModuleRegistry::SOCIOS, enabled: true);
        $this->createFeature($module, 'filament.members', enabled: false, isCore: true);

        FeatureRegistry::clearCache();
        ModuleRegistry::clearCache();

        $this->assertTrue(FeatureRegistry::enabled('filament.members'));
    }

    public function test_status_badge_shows_warning_when_module_on_feature_off(): void
    {
        $module = $this->catalogModule(ModuleRegistry::CARTOES, enabled: true);
        $feature = $this->createFeature($module, 'filament.cards', enabled: false);

        $this->assertSame('Desactivada', $feature->statusBadge()['label']);
        $this->assertSame('warning', $feature->statusBadge()['color']);
        $this->assertTrue($feature->canToggleEnabled());
        $this->assertFalse($feature->isEffectivelyEnabled());
    }

    public function test_status_badge_shows_gray_when_module_off(): void
    {
        $module = $this->catalogModule(ModuleRegistry::CARTOES, enabled: false);
        $feature = $this->createFeature($module, 'filament.cards', enabled: true);

        $this->assertSame('Módulo desactivado', $feature->statusBadge()['label']);
        $this->assertFalse($feature->canToggleEnabled());
    }

    protected function catalogModule(string $slug, bool $enabled): Module
    {
        $definition = ModuleRegistry::catalog()[$slug];

        return Module::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'label' => $definition['label'],
                'description' => $definition['description'] ?? null,
                'enabled' => $enabled,
                'is_core' => (bool) ($definition['core'] ?? false),
                'sort_order' => 10,
            ],
        );
    }

    protected function createFeature(Module $module, string $key, bool $enabled, bool $isCore = false): ModuleFeature
    {
        $definition = FeatureRegistry::catalog()[$key];

        return ModuleFeature::query()->create([
            'module_id' => $module->id,
            'key' => $key,
            'label' => $definition['label'],
            'binding_type' => $definition['binding_type'] ?? null,
            'binding_target' => $definition['binding_target'] ?? null,
            'is_system' => true,
            'enabled' => $enabled,
            'is_core' => $isCore,
            'sort_order' => 10,
        ]);
    }
}
