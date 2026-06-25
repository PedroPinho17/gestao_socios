<?php

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class MemberAccountService
{
    public function hasAccount(Member $member): bool
    {
        return User::query()->where('member_id', $member->id)->exists();
    }

    public function accountFor(Member $member): ?User
    {
        return User::query()->where('member_id', $member->id)->first();
    }

    /**
     * @throws ValidationException
     */
    public function createOrUpdate(
        Member $member,
        string $email,
        ?string $password = null,
        ?string $name = null,
    ): User {
        $name = $name ?? $member->nome;
        $isNew = ! $this->hasAccount($member);

        if ($isNew && blank($password)) {
            throw ValidationException::withMessages([
                'password' => ['Password em falta.'],
            ]);
        }

        $rules = [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
        ];

        if (filled($password)) {
            $rules['password'] = [Password::defaults()];
        }

        Validator::make(
            ['email' => $email, 'password' => $password, 'name' => $name],
            $rules,
        )->validate();

        if ($this->emailBelongsToAnotherUser($member, $email)) {
            throw ValidationException::withMessages([
                'email' => ['Este email já está associado a outro utilizador.'],
            ]);
        }

        $attributes = [
            'name' => $name,
            'email' => $email,
            'permissao_id' => null,
        ];

        if (filled($password)) {
            $attributes['password'] = Hash::make($password);
            $attributes['must_change_password'] = true;
        }

        return User::query()->updateOrCreate(
            ['member_id' => $member->id],
            $attributes,
        );
    }

    private function emailBelongsToAnotherUser(Member $member, string $email): bool
    {
        return User::query()
            ->where('email', $email)
            ->where(function ($query) use ($member) {
                $query->whereNull('member_id')
                    ->orWhere('member_id', '!=', $member->id);
            })
            ->exists();
    }
}
