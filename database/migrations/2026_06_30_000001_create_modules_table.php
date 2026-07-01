<?php

use App\Models\AppSetting;
use App\Support\ModuleRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('disabled_message')->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('is_core')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $sort = 0;
        $legacy = AppSetting::json(AppSetting::MODULOS_ATIVOS);

        foreach (ModuleRegistry::catalog() as $slug => $definition) {
            $enabled = true;

            if (is_array($legacy) && array_key_exists($slug, $legacy)) {
                $enabled = filter_var($legacy[$slug], FILTER_VALIDATE_BOOLEAN);
            }

            if ($definition['core'] ?? false) {
                $enabled = true;
            }

            DB::table('modules')->insert([
                'slug' => $slug,
                'label' => $definition['label'],
                'description' => $definition['description'] ?? null,
                'disabled_message' => $definition['disabled_message'] ?? null,
                'enabled' => $enabled,
                'is_core' => (bool) ($definition['core'] ?? false),
                'sort_order' => ++$sort * 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
