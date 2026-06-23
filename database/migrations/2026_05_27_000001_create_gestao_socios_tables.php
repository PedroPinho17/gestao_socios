<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quota_plans', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('periodicidade', 20);
            $table->decimal('valor', 10, 2);
            $table->string('tipo_vencimento', 20)->default('aniversario');
            $table->unsignedTinyInteger('dia_vencimento_mes')->default(1);
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('nome');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->date('data_adesao');
            $table->foreignId('quota_plan_id')->nullable()->constrained('quota_plans')->nullOnDelete();
            $table->string('foto_path')->nullable();
            $table->boolean('ativo')->default(true);
            $table->text('notas')->nullable();
            $table->string('cargo_cartao')->nullable();
            $table->date('validade_manual')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->date('data');
            $table->decimal('valor', 10, 2);
            $table->string('referencia');
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('club_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nome_clube')->default('O meu clube');
            $table->string('logo_path')->nullable();
            $table->string('card_gradient_from', 7)->default('#0f766e');
            $table->string('card_gradient_to', 7)->default('#0f172a');
            $table->string('card_accent_color', 7)->default('#d1fae5');
            $table->string('card_titulo')->default('Sócio');
            $table->string('card_campo_extra_label')->default('Cargo');
            $table->boolean('show_proximo_vencimento')->default(true);
            $table->boolean('show_cargo')->default(true);
            $table->boolean('show_email')->default(false);
            $table->boolean('show_telefone')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('members');
        Schema::dropIfExists('quota_plans');
        Schema::dropIfExists('club_settings');
    }
};
