<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            // Data de vencimento para a qual já foi enviado o lembrete (evita repetir).
            $table->date('quota_reminder_due')->nullable()->after('validade_manual');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropColumn('quota_reminder_due');
        });
    }
};
