<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            AppSetting::MFA_OBRIGATORIO => '0',
            AppSetting::DIAS_ALERTA_QUOTA => '7',
            AppSetting::LEMBRETES_AUTOMATICOS => '0',
            AppSetting::PASSKEYS_ATIVAS => '1',
        ];

        foreach ($defaults as $chave => $valor) {
            AppSetting::query()->firstOrCreate(
                ['chave' => $chave],
                ['valor' => $valor],
            );
        }
    }
}
