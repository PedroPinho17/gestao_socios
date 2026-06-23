<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('admin')->after('password');
            $table->boolean('must_change_password')->default(false)->after('role');
            $table->timestamp('password_changed_at')->nullable()->after('must_change_password');
            $table->text('app_authentication_secret')->nullable()->after('password_changed_at');
            $table->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'must_change_password',
                'password_changed_at',
                'app_authentication_secret',
                'app_authentication_recovery_codes',
            ]);
        });
    }
};
