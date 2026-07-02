<?php

namespace Tests\Feature\Api;

use App\Enums\QuotaSituationKind;
use App\Models\AppSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class MemberQuotaApiTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_member_can_fetch_quota_status(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-28'));
        AppSetting::set(AppSetting::DIAS_ALERTA_QUOTA, '7');

        $member = $this->createMember();
        $this->createPayment($member, ['data' => '2025-01-01', 'referencia' => '2025-01']);
        $user = $this->createMemberUser($member);
        $token = $this->memberApiToken($user);

        $this->withToken($token)
            ->getJson('/api/me/quota')
            ->assertOk()
            ->assertJsonPath('status', QuotaSituationKind::DueSoon->value)
            ->assertJsonPath('plan.nome', $member->quotaPlan->nome);
    }

    public function test_quota_endpoint_requires_password_change_first(): void
    {
        $member = $this->createMember();
        $user = $this->createMemberUser($member, ['must_change_password' => true]);
        $token = $this->memberApiToken($user);

        $this->withToken($token)
            ->getJson('/api/me/quota')
            ->assertForbidden()
            ->assertJsonPath('message', 'Deve alterar a password antes de continuar.');
    }

    public function test_quota_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/me/quota')->assertUnauthorized();
    }
}
