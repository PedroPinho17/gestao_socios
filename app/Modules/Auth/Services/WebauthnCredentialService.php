<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use LaravelWebauthn\Facades\Webauthn;

class WebauthnCredentialService
{
    /**
     * @return array{id: string, rawId: string, type: string, response: array<string, mixed>}
     */
    public function credentialsFromRequest(Request $request): array
    {
        return $request->only(['id', 'rawId', 'response', 'type']);
    }

    public function userFromCredentials(array $credentials): ?User
    {
        $provider = $this->userProvider();
        $user = $provider->retrieveByCredentials($credentials);

        return $user instanceof User ? $user : null;
    }

    public function validateCredentials(User $user, array $credentials): bool
    {
        return $this->userProvider()->validateCredentials($user, $credentials);
    }

    /**
     * @return array<string, mixed>
     */
    public function assertionOptionsFor(User $user): array
    {
        $publicKey = Webauthn::prepareAssertion($user);

        return json_decode(json_encode($publicKey), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function attestationOptionsFor(User $user): array
    {
        $publicKey = Webauthn::prepareAttestation($user);

        return json_decode(json_encode($publicKey), true, 512, JSON_THROW_ON_ERROR);
    }

    public function registerKey(User $user, array $credentials, string $name): Model
    {
        return Webauthn::validateAttestation($user, $credentials, $name);
    }

    public function assertMemberLogin(Request $request): User
    {
        $credentials = $this->credentialsFromRequest($request);
        $user = $this->userFromCredentials($credentials);

        if ($user === null || ! $user->isMember() || ! $this->validateCredentials($user, $credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('webauthn::errors.login_failed')],
            ]);
        }

        return $user;
    }

    public function assertStaffLogin(Request $request): ?User
    {
        $credentials = $this->credentialsFromRequest($request);
        $user = $this->userFromCredentials($credentials);

        if ($user === null || ! $user->isStaff() || ! $this->validateCredentials($user, $credentials)) {
            return null;
        }

        return $user;
    }

    private function userProvider(): UserProvider
    {
        return Auth::createUserProvider('users');
    }
}
