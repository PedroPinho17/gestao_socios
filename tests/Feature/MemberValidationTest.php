<?php

namespace Tests\Feature;

use App\Enums\QuotaSituationKind;
use App\Modules\Members\Http\Controllers\MemberValidationController;
use App\Modules\Members\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class MemberValidationTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        QuotaService::clearSituationCache();
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_signed_validation_url_is_accepted(): void
    {
        $member = $this->createMember(['numero' => '9001']);
        $url = URL::signedRoute('member.validate', ['member' => $member->getKey()]);

        $this->get($url)->assertOk();
    }

    public function test_validation_controller_computes_quota_status_for_active_member(): void
    {
        $member = $this->createMember(['nome' => 'Estado Quota', 'ativo' => true]);
        $this->createPayment($member, ['data' => '2025-02-20', 'referencia' => '2025-02']);

        Carbon::setTestNow(Carbon::parse('2025-02-25'));

        $view = app(MemberValidationController::class)
            ->show($member->fresh(), app(QuotaService::class));

        $this->assertSame('Quota em dia', $view->getData()['statusLabel']);
        $this->assertSame(QuotaSituationKind::Ok, $view->getData()['situation']['kind']);
        $this->assertSame('ok', $view->getData()['statusTone']);
    }

    public function test_unsigned_validation_url_is_rejected(): void
    {
        $member = $this->createMember();

        $this->get(route('member.validate', ['member' => $member]))
            ->assertForbidden();
    }

    public function test_inactive_member_shows_inactive_label(): void
    {
        $member = $this->createMember(['ativo' => false, 'nome' => 'Inactivo']);

        $view = app(MemberValidationController::class)
            ->show($member->fresh(), app(QuotaService::class));

        $this->assertSame('Sócio inativo', $view->getData()['statusLabel']);
        $this->assertSame(QuotaSituationKind::Inativo, $view->getData()['situation']['kind']);
        $this->assertSame('neutral', $view->getData()['statusTone']);
    }
}
