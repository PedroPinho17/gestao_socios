<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use App\Models\Module;
use App\Support\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        ModuleRegistry::clearCache();
        parent::tearDown();
    }

    public function test_enabled_returns_true_for_active_module(): void
    {
        Module::query()->where('slug', ModuleRegistry::RELATORIOS)->update(['enabled' => true]);
        ModuleRegistry::clearCache();

        $this->assertTrue(ModuleRegistry::enabled(ModuleRegistry::RELATORIOS));
    }

    public function test_enabled_returns_false_for_disabled_module(): void
    {
        Module::query()->where('slug', ModuleRegistry::RELATORIOS)->update(['enabled' => false]);
        ModuleRegistry::clearCache();

        $this->assertFalse(ModuleRegistry::enabled(ModuleRegistry::RELATORIOS));
    }

    public function test_core_module_stays_enabled_even_when_flag_is_off(): void
    {
        Module::query()->where('slug', ModuleRegistry::SOCIOS)->update(['enabled' => false]);
        ModuleRegistry::clearCache();

        $this->assertTrue(ModuleRegistry::enabled(ModuleRegistry::SOCIOS));
    }

    public function test_disabled_message_uses_module_custom_message(): void
    {
        Module::query()->where('slug', ModuleRegistry::AREA_SOCIO)->update([
            'disabled_message' => 'Área indisponível para testes.',
        ]);
        ModuleRegistry::clearCache();

        $this->assertSame('Área indisponível para testes.', ModuleRegistry::disabledMessage(ModuleRegistry::AREA_SOCIO));
    }

    public function test_active_map_reflects_database_state(): void
    {
        Module::query()->where('slug', ModuleRegistry::CARTOES)->update(['enabled' => false]);
        ModuleRegistry::clearCache();

        $map = ModuleRegistry::activeMap();

        $this->assertFalse($map[ModuleRegistry::CARTOES]);
        $this->assertTrue($map[ModuleRegistry::SOCIOS]);
    }

    public function test_sync_catalog_creates_missing_modules(): void
    {
        Module::query()->where('slug', ModuleRegistry::LEMBRETES)->delete();
        ModuleRegistry::clearCache();

        $created = ModuleRegistry::syncCatalog();

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('modules', ['slug' => ModuleRegistry::LEMBRETES]);
    }

    public function test_legacy_modulos_ativos_fallback_when_slug_missing_in_database(): void
    {
        Module::query()->where('slug', ModuleRegistry::COMUNICACOES)->delete();
        ModuleRegistry::clearCache();

        AppSetting::setJson(AppSetting::MODULOS_ATIVOS, [
            ModuleRegistry::COMUNICACOES => false,
        ]);

        $created = ModuleRegistry::syncCatalog();

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('modules', [
            'slug' => ModuleRegistry::COMUNICACOES,
            'enabled' => false,
        ]);
    }
}
