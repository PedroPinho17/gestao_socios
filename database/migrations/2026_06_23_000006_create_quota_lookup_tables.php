<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodicidades', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('nome');
            $table->unsignedTinyInteger('meses');
            $table->unsignedTinyInteger('ordem')->default(0);
            $table->timestamps();
        });

        Schema::create('tipos_vencimento_quota', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('nome');
            $table->unsignedTinyInteger('ordem')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('periodicidades')->insert([
            ['id' => 1, 'slug' => 'mensal', 'nome' => 'Mensal', 'meses' => 1, 'ordem' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'slug' => 'trimestral', 'nome' => 'Trimestral', 'meses' => 3, 'ordem' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'slug' => 'semestral', 'nome' => 'Semestral', 'meses' => 6, 'ordem' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'slug' => 'anual', 'nome' => 'Anual', 'meses' => 12, 'ordem' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('tipos_vencimento_quota')->insert([
            ['id' => 1, 'slug' => 'aniversario', 'nome' => 'Desde último pagamento (aniversário)', 'ordem' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'slug' => 'dia_fixo', 'nome' => 'Dia fixo no mês', 'ordem' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('quota_plans', function (Blueprint $table) {
            $table->foreignId('periodicidade_id')->nullable()->after('nome')->constrained('periodicidades');
            $table->foreignId('tipo_vencimento_quota_id')->nullable()->after('valor')->constrained('tipos_vencimento_quota');
        });

        $periodicidadeMap = [
            'mensal' => 1,
            'trimestral' => 2,
            'semestral' => 3,
            'anual' => 4,
        ];

        $tipoMap = [
            'aniversario' => 1,
            'dia_fixo' => 2,
        ];

        foreach (DB::table('quota_plans')->get() as $plan) {
            DB::table('quota_plans')
                ->where('id', $plan->id)
                ->update([
                    'periodicidade_id' => $periodicidadeMap[$plan->periodicidade] ?? 1,
                    'tipo_vencimento_quota_id' => $tipoMap[$plan->tipo_vencimento] ?? 1,
                ]);
        }

        Schema::table('quota_plans', function (Blueprint $table) {
            $table->dropColumn(['periodicidade', 'tipo_vencimento']);
        });
    }

    public function down(): void
    {
        Schema::table('quota_plans', function (Blueprint $table) {
            $table->string('periodicidade', 20)->default('mensal')->after('nome');
            $table->string('tipo_vencimento', 20)->default('aniversario')->after('valor');
        });

        $periodicidadeMap = [
            1 => 'mensal',
            2 => 'trimestral',
            3 => 'semestral',
            4 => 'anual',
        ];

        $tipoMap = [
            1 => 'aniversario',
            2 => 'dia_fixo',
        ];

        foreach (DB::table('quota_plans')->get() as $plan) {
            DB::table('quota_plans')
                ->where('id', $plan->id)
                ->update([
                    'periodicidade' => $periodicidadeMap[$plan->periodicidade_id] ?? 'mensal',
                    'tipo_vencimento' => $tipoMap[$plan->tipo_vencimento_quota_id] ?? 'aniversario',
                ]);
        }

        Schema::table('quota_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('periodicidade_id');
            $table->dropConstrainedForeignId('tipo_vencimento_quota_id');
        });

        Schema::dropIfExists('tipos_vencimento_quota');
        Schema::dropIfExists('periodicidades');
    }
};
