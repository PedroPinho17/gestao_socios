<?php

namespace Tests\Feature\Api;

use App\Modules\Auth\Models\Permissao;
use App\Support\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class MemberAuthApiTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    public function test_member_can_login_and_fetch_profile(): void
    {
        $member = $this->createMember(['email' => 'login@test.pt']);
        $user = $this->createMemberUser($member, ['email' => 'login@test.pt']);

        $login = $this->postJson('/api/login', [
            'email' => 'login@test.pt',
            'password' => 'password',
        ]);

        $login
            ->assertOk()
            ->assertJsonPath('user.numero', $member->numero)
            ->assertJsonPath('user.must_change_password', false);

        $token = $login->json('token');

        $this->withToken($token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('numero', $member->numero)
            ->assertJsonPath('nome', $member->nome);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $member = $this->createMember(['email' => 'wrong@test.pt']);
        $this->createMemberUser($member, ['email' => 'wrong@test.pt']);

        $this->postJson('/api/login', [
            'email' => 'wrong@test.pt',
            'password' => 'incorrect',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_rejects_staff_accounts(): void
    {
        $staff = $this->createStaffUser(Permissao::ADMINISTRADOR);

        $this->postJson('/api/login', [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_logout_revokes_current_token(): void
    {
        $member = $this->createMember();
        $user = $this->createMemberUser($member);
        $token = $this->memberApiToken($user);

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_member_can_change_required_password_and_receive_new_token(): void
    {
        $member = $this->createMember();
        $user = $this->createMemberUser($member, ['must_change_password' => true]);
        $token = $this->memberApiToken($user);

        $response = $this->withToken($token)->putJson('/api/me/password', [
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('must_change_password', false);

        $newToken = $response->json('token');

        $this->withToken($newToken)
            ->getJson('/api/me/quota')
            ->assertOk();
    }

    public function test_login_is_blocked_when_member_area_module_disabled(): void
    {
        $this->setModuleEnabled(ModuleRegistry::AREA_SOCIO, false);

        $member = $this->createMember(['email' => 'blocked@test.pt']);
        $this->createMemberUser($member, ['email' => 'blocked@test.pt']);

        $this->postJson('/api/login', [
            'email' => 'blocked@test.pt',
            'password' => 'password',
        ])->assertForbidden()
            ->assertJsonPath('module', ModuleRegistry::AREA_SOCIO);
    }
}
