<?php

namespace Tests\Feature\Api;

use App\Support\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class MemberPaymentsApiTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    public function test_member_can_list_payments(): void
    {
        $member = $this->createMember();
        $payment = $this->createPayment($member, ['referencia' => '2026-04', 'valor' => 20]);
        $user = $this->createMemberUser($member);
        $token = $this->memberApiToken($user);

        $this->withToken($token)
            ->getJson('/api/me/payments')
            ->assertOk()
            ->assertJsonPath('data.0.referencia', '2026-04')
            ->assertJsonPath('data.0.valor', 20);
    }

    public function test_member_can_download_payment_receipt_when_module_enabled(): void
    {
        $this->setModuleEnabled(ModuleRegistry::COMPROVATIVOS, true);

        $member = $this->createMember();
        $payment = $this->createPayment($member);
        $user = $this->createMemberUser($member);
        $token = $this->memberApiToken($user);

        $response = $this->withToken($token)
            ->get('/api/me/payments/'.$payment->id.'/receipt');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_receipt_download_is_blocked_when_comprovativos_module_disabled(): void
    {
        $this->setModuleEnabled(ModuleRegistry::COMPROVATIVOS, false);

        $member = $this->createMember();
        $payment = $this->createPayment($member);
        $user = $this->createMemberUser($member);
        $token = $this->memberApiToken($user);

        $this->withToken($token)
            ->get('/api/me/payments/'.$payment->id.'/receipt')
            ->assertForbidden()
            ->assertJsonPath('module', ModuleRegistry::COMPROVATIVOS);
    }
}
