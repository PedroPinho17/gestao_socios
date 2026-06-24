<?php

use App\Support\MemberCardLayout;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->json('card_layout')->nullable()->after('show_telefone');
        });

        foreach (DB::table('club_settings')->get() as $row) {
            DB::table('club_settings')
                ->where('id', $row->id)
                ->update([
                    'card_layout' => json_encode(MemberCardLayout::defaultsFromRow($row)),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->dropColumn('card_layout');
        });
    }
};
