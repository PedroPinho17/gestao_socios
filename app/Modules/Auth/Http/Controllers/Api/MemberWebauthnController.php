<?php

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\WebauthnCredentialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LaravelWebauthn\Facades\Webauthn;
use LaravelWebauthn\Models\WebauthnKey;

class MemberWebauthnController extends Controller
{
    public function __construct(
        private readonly WebauthnCredentialService $webauthn,
    ) {}

    public function loginOptions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()->where('email', $data['email'])->first();

        if ($user === null || ! $user->isMember() || ! Webauthn::enabled($user)) {
            throw ValidationException::withMessages([
                'email' => [__('webauthn::errors.login_failed')],
            ]);
        }

        return response()->json([
            'publicKey' => $this->webauthn->assertionOptionsFor($user),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $user = $this->webauthn->assertMemberLogin($request);
        $user->loadMissing('member');

        $token = $user->createToken('member-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'nome' => $user->member->nome,
                'numero' => $user->member->numero,
                'must_change_password' => $user->must_change_password,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->memberUser($request);

        return response()->json([
            'keys' => $user->webauthnKeys()
                ->orderByDesc('created_at')
                ->get(['id', 'name', 'created_at']),
        ]);
    }

    public function storeOptions(Request $request): JsonResponse
    {
        $user = $this->memberUser($request);

        return response()->json([
            'publicKey' => $this->webauthn->attestationOptionsFor($user),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->memberUser($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'id' => ['required', 'string'],
            'rawId' => ['required', 'string'],
            'type' => ['required', 'string'],
            'response' => ['required', 'array'],
        ]);

        $key = $this->webauthn->registerKey(
            $user,
            $this->webauthn->credentialsFromRequest($request),
            $data['name'],
        );

        return response()->json([
            'key' => $key->only(['id', 'name', 'created_at']),
            'message' => 'Chave de acesso registada.',
        ], 201);
    }

    public function destroy(Request $request, int $key): JsonResponse
    {
        $user = $this->memberUser($request);

        $record = WebauthnKey::query()
            ->whereKey($key)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $record->delete();

        return response()->json([
            'message' => 'Chave removida.',
        ]);
    }

    private function memberUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isMember()) {
            abort(403);
        }

        return $user;
    }
}
