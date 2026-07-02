<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_features', function (Blueprint $table): void {
            $table->boolean('enabled')->default(true)->after('is_system');
            $table->boolean('is_core')->default(false)->after('enabled');
        });

        if (Schema::hasTable('module_features')) {
            DB::table('module_features')->update(['enabled' => true]);

            DB::table('module_features')
                ->where('key', 'filament.members')
                ->update(['is_core' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('module_features', function (Blueprint $table): void {
            $table->dropColumn(['enabled', 'is_core']);
        });
    }
};
