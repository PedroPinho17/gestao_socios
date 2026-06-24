<?php

namespace Database\Seeders;

use App\Models\ClubSetting;
use App\Models\Permissao;
use App\Models\Periodicidade;
use App\Models\QuotaPlan;
use App\Models\TipoVencimentoQuota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissaoSeeder::class);
        $this->call(AppSettingSeeder::class);

        if (app()->environment('local')) {
            $this->seedDemoUsers();
        }

        ClubSetting::current();

        QuotaPlan::query()->firstOrCreate(
            ['nome' => 'Quota social — mensal'],
            [
                'periodicidade_id' => Periodicidade::query()->where('slug', 'mensal')->value('id') ?? 1,
                'valor' => 15,
                'tipo_vencimento_quota_id' => TipoVencimentoQuota::query()->where('slug', 'aniversario')->value('id') ?? 1,
                'dia_vencimento_mes' => 1,
            ],
        );
    }

    private function seedDemoUsers(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'imperador@dev.local'],
            [
                'name' => 'Imperador',
                'password' => Hash::make('password'),
                'permissao_id' => Permissao::IMPERADOR,
                'must_change_password' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@clube.pt'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'permissao_id' => Permissao::ADMINISTRADOR,
                'must_change_password' => true,
            ],
        );

        $this->command?->warn('Contas demo (local): imperador@dev.local e admin@clube.pt — password "password".');
        $this->command?->warn('Em produção use: php artisan gestao:create-imperador seu@email.pt');
    }
}
