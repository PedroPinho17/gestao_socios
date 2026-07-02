<?php

namespace Tests\Unit;

use App\Models\AppSetting;
use App\Modules\Members\Services\QuotaService;
use App\Modules\Reports\Services\PayingMembersReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class PayingMembersReportServiceTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        QuotaService::clearSituationCache();
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_rows_include_ok_and_due_soon_members_sorted_by_due_date(): void
    {
        AppSetting::set(AppSetting::DIAS_ALERTA_QUOTA, '7');
        Carbon::setTestNow(Carbon::parse('2025-02-25'));

        $dueSoon = $this->createMember(['numero' => '1', 'nome' => 'A vencer']);
        $this->createPayment($dueSoon, ['data' => '2025-02-01', 'referencia' => '2025-02']);

        $ok = $this->createMember(['numero' => '2', 'nome' => 'Em dia']);
        $this->createPayment($ok, ['data' => '2025-02-20', 'referencia' => '2025-02']);

        $overdue = $this->createMember(['numero' => '3', 'nome' => 'Em atraso']);
        $this->createPayment($overdue, ['data' => '2024-01-01', 'referencia' => '2024-01']);

        QuotaService::clearSituationCache();

        $rows = app(PayingMembersReportService::class)->rows();

        $this->assertCount(2, $rows);
        $this->assertSame(['A vencer', 'Em dia'], $rows->pluck('nome')->all());
        $this->assertSame('A vencer', $rows->first()['situacao']);
        $this->assertSame('Em dia', $rows->last()['situacao']);
    }
}
