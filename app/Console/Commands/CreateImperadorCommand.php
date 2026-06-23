<?php

namespace App\Console\Commands;

use App\Models\Permissao;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateImperadorCommand extends Command
{
    protected $signature = 'gestao:create-imperador
                            {email : Email do imperador}
                            {--name=Imperador : Nome a apresentar}
                            {--password= : Password (será pedida se omitida)}';

    protected $description = 'Cria a conta imperador (acesso total à plataforma)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name');
        $password = $this->option('password') ?? $this->secret('Password');

        if (blank($password)) {
            $this->error('Password em falta.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => ['required', 'email', 'max:255'],
                'password' => [Password::defaults()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'permissao_id' => Permissao::IMPERADOR,
                'must_change_password' => false,
                'password_changed_at' => now(),
            ],
        );

        $this->info("Conta imperador criada/atualizada: {$user->email}");

        return self::SUCCESS;
    }
}
