<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->string('permissao')->unique();
            $table->timestamps();
        });

        DB::table('permissoes')->insert([
            ['id' => 1, 'permissao' => 'Imperador', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'permissao' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'permissao' => 'Tesoureiro', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('permissao_id')
                ->nullable()
                ->after('password')
                ->constrained('permissoes');
        });

        if (Schema::hasColumn('users', 'role')) {
            $roleMap = [
                'imperador' => 1,
                'admin' => 2,
                'treasurer' => 3,
            ];

            foreach ($roleMap as $role => $permissaoId) {
                DB::table('users')
                    ->where('role', $role)
                    ->update(['permissao_id' => $permissaoId]);
            }

            DB::table('users')
                ->whereNull('permissao_id')
                ->update(['permissao_id' => 2]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('admin')->after('password');
        });

        $roleMap = [
            1 => 'imperador',
            2 => 'admin',
            3 => 'treasurer',
        ];

        foreach ($roleMap as $permissaoId => $role) {
            DB::table('users')
                ->where('permissao_id', $permissaoId)
                ->update(['role' => $role]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('permissao_id');
        });

        Schema::dropIfExists('permissoes');
    }
};
