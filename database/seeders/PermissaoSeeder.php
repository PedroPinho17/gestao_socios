<?php

namespace Database\Seeders;

use App\Models\Permissao;
use Illuminate\Database\Seeder;

class PermissaoSeeder extends Seeder
{
    public function run(): void
    {
        $permissoes = [
            Permissao::IMPERADOR => 'Imperador',
            Permissao::ADMINISTRADOR => 'Administrador',
            Permissao::TESOUREIRO => 'Tesoureiro',
        ];

        foreach ($permissoes as $id => $nome) {
            Permissao::query()->updateOrCreate(
                ['id' => $id],
                ['permissao' => $nome],
            );
        }
    }
}
