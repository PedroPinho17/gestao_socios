<?php

namespace Tests\Unit;

use App\Enums\QuotaSituationKind;
use App\Models\AppSetting;
use App\Modules\Members\Models\QuotaPlan;
use App\Modules\Members\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class QuotaServiceTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    private QuotaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(QuotaService::class);
        QuotaService::clearSituationCache();
    }

    protected function tearDown(): void
    {
        QuotaService::clearSituationCache();
        parent::tearDown();
    }

    public function test_inactive_member_returns_inativo(): void
    {
        $member = $this->createMember(['ativo' => false]);

        $situation = $this->service->getSituation($member, Carbon::parse('2025-06-01'));

        $this->assertSame(QuotaSituationKind::Inativo, $situation['kind']);
    }

    public function test_member_without_plan_returns_sem_plano(): void
    {
        $member = $this->createMember(['quota_plan_id' => null]);

        $situation = $this->service->getSituation($member, Carbon::parse('2025-06-01'));

        $this->assertSame(QuotaSituationKind::SemPlano, $situation['kind']);
    }

    public function test_member_with_overdue_payment_returns_overdue(): void
    {
        $member = $this->createMember();
        $this->createPayment($member, ['data' => '2025-01-01', 'referencia' => '2025-01']);

        $situation = $this->service->getSituation($member, Carbon::parse('2025-03-01'));

        $this->assertSame(QuotaSituationKind::Overdue, $situation['kind']);
        $this->assertSame(28, $situation['days_overdue']);
        $this->assertEquals(Carbon::parse('2025-02-01'), $situation['next_due']);
    }

    public function test_member_due_soon_within_alert_window(): void
    {
        AppSetting::set(AppSetting::DIAS_ALERTA_QUOTA, '7');

        $member = $this->createMember();
        $this->createPayment($member, ['data' => '2025-01-01', 'referencia' => '2025-01']);

        $situation = $this->service->getSituation($member, Carbon::parse('2025-01-28'));

        $this->assertSame(QuotaSituationKind::DueSoon, $situation['kind']);
        $this->assertSame(4, $situation['days_until']);
    }

    public function test_member_with_recent_payment_returns_ok(): void
    {
        AppSetting::set(AppSetting::DIAS_ALERTA_QUOTA, '7');

        $member = $this->createMember();
        $this->createPayment($member, ['data' => '2025-01-01', 'referencia' => '2025-01']);

        $situation = $this->service->getSituation($member, Carbon::parse('2025-01-15'));

        $this->assertSame(QuotaSituationKind::Ok, $situation['kind']);
        $this->assertEquals(Carbon::parse('2025-02-01'), $situation['next_due']);
    }

    public function test_compute_next_due_date_uses_anniversary_from_last_payment(): void
    {
        $plan = QuotaPlan::query()->with(['periodicidade', 'tipoVencimento'])->findOrFail(
            $this->createQuotaPlan(['periodicidade_id' => 1])->id,
        );

        $nextDue = $this->service->computeNextDueDate($plan, Carbon::parse('2024-05-10'));

        $this->assertEquals(Carbon::parse('2024-06-10'), $nextDue);
    }

    public function test_compute_next_due_date_uses_fixed_day_in_month(): void
    {
        $plan = QuotaPlan::query()->with(['periodicidade', 'tipoVencimento'])->findOrFail(
            $this->createQuotaPlan([
                'tipo_vencimento_quota_id' => 2,
                'dia_vencimento_mes' => 15,
            ])->id,
        );

        $nextDue = $this->service->computeNextDueDate($plan, Carbon::parse('2024-01-10'));

        $this->assertEquals(Carbon::parse('2024-02-15'), $nextDue);
    }

    public function test_resumo_vencimento_plano_describes_plan_type(): void
    {
        $anniversary = QuotaPlan::query()->with('tipoVencimento')->findOrFail($this->createQuotaPlan()->id);
        $fixed = QuotaPlan::query()->with('tipoVencimento')->findOrFail(
            $this->createQuotaPlan(['tipo_vencimento_quota_id' => 2, 'dia_vencimento_mes' => 20])->id,
        );

        $this->assertSame('Aniversário', $this->service->resumoVencimentoPlano($anniversary));
        $this->assertSame('Dia 20', $this->service->resumoVencimentoPlano($fixed));
    }

    public function test_alert_lists_group_overdue_and_due_soon_members(): void
    {
        AppSetting::set(AppSetting::DIAS_ALERTA_QUOTA, '7');

        $overdueMember = $this->createMember(['nome' => 'Em atraso']);
        $this->createPayment($overdueMember, ['data' => '2024-01-01', 'referencia' => '2024-01']);

        $dueSoonMember = $this->createMember(['nome' => 'A vencer']);
        $this->createPayment($dueSoonMember, ['data' => '2025-02-01', 'referencia' => '2025-02']);

        $okMember = $this->createMember(['nome' => 'Em dia']);
        $this->createPayment($okMember, ['data' => '2025-02-20', 'referencia' => '2025-02']);

        QuotaService::clearSituationCache();
        Carbon::setTestNow(Carbon::parse('2025-02-25'));

        $lists = $this->service->alertLists();

        $this->assertTrue($lists['overdue']->pluck('nome')->contains('Em atraso'));
        $this->assertFalse($lists['overdue']->pluck('nome')->contains('Em dia'));
        $this->assertTrue($lists['dueSoon']->pluck('nome')->contains('A vencer'));

        Carbon::setTestNow();
    }

    public function test_filter_member_ids_by_situation_returns_matching_ids(): void
    {
        $overdueMember = $this->createMember();
        $this->createPayment($overdueMember, ['data' => '2024-01-01', 'referencia' => '2024-01']);

        QuotaService::clearSituationCache();
        Carbon::setTestNow(Carbon::parse('2025-03-01'));

        $ids = $this->service->filterMemberIdsBySituation('overdue');

        $this->assertTrue($ids->contains($overdueMember->id));

        Carbon::setTestNow();
    }
}
