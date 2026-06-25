<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Payment;
use App\Models\QuotaPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberDemoSeeder extends Seeder
{
    public function run(): void
    {
        $plan = QuotaPlan::query()->where('nome', 'Quota social — mensal')->first();

        if (! $plan) {
            $this->command?->warn('Plano de quota demo não encontrado — execute DatabaseSeeder primeiro.');

            return;
        }

        $member = Member::query()->updateOrCreate(
            ['numero' => '1'],
            [
                'nome' => 'João Sócio Demo',
                'email' => 'joao@clube.pt',
                'telefone' => '912345678',
                'data_adesao' => Carbon::parse('2025-01-15'),
                'quota_plan_id' => $plan->id,
                'ativo' => true,
            ],
        );

        $payments = [
            ['data' => '2025-11-01', 'valor' => 15, 'referencia' => '2025-11'],
            ['data' => '2025-12-01', 'valor' => 15, 'referencia' => '2025-12'],
            ['data' => '2026-01-01', 'valor' => 15, 'referencia' => '2026-01'],
        ];

        foreach ($payments as $payment) {
            Payment::query()->updateOrCreate(
                [
                    'member_id' => $member->id,
                    'referencia' => $payment['referencia'],
                ],
                [
                    'data' => $payment['data'],
                    'valor' => $payment['valor'],
                    'notas' => 'Pagamento demo',
                ],
            );
        }

        User::query()->updateOrCreate(
            ['email' => 'socio@test.pt'],
            [
                'name' => $member->nome,
                'password' => Hash::make('password'),
                'permissao_id' => null,
                'member_id' => $member->id,
                'must_change_password' => false,
                'password_changed_at' => now(),
            ],
        );

        $this->command?->info('Sócio demo: n.º 1 — login socio@test.pt / password');
    }
}
