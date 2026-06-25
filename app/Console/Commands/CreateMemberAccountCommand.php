<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Services\MemberAccountService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class CreateMemberAccountCommand extends Command
{
    protected $signature = 'gestao:create-member-account
                            {member : ID ou número do sócio}
                            {email : Email de login}
                            {--password= : Password (será pedida se omitida)}
                            {--name= : Nome do utilizador (default: nome do sócio)}';

    protected $description = 'Cria conta de login para um sócio (área do sócio / API)';

    public function handle(MemberAccountService $service): int
    {
        $member = $this->resolveMember($this->argument('member'));

        if (! $member) {
            $this->error('Sócio não encontrado.');

            return self::FAILURE;
        }

        $email = $this->argument('email');
        $name = $this->option('name');
        $password = $this->option('password') ?? $this->secret('Password (mín. 12 caracteres)');

        if (blank($password)) {
            $this->error('Password em falta.');

            return self::FAILURE;
        }

        try {
            $user = $service->createOrUpdate($member, $email, $password, $name);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $this->info("Conta de sócio criada/atualizada: {$user->email} (sócio n.º {$member->numero})");

        if ($this->option('password') === null) {
            $this->warn('Guarde a password — não será mostrada novamente.');
        }

        return self::SUCCESS;
    }

    private function resolveMember(string $identifier): ?Member
    {
        if (is_numeric($identifier)) {
            return Member::query()->find((int) $identifier)
                ?? Member::query()->where('numero', $identifier)->first();
        }

        return Member::query()->where('numero', $identifier)->first();
    }
}
