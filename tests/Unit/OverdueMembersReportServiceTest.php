<?php

namespace Tests\Unit;

use App\Modules\Members\Services\QuotaService;
use App\Modules\Reports\Services\OverdueMembersReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class OverdueMembersReportServiceTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        QuotaService::clearSituationCache();
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_rows_include_only_overdue_members_sorted_by_days_overdue(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-06-01'));

        $lessOverdue = $this->createMember(['numero' => '10', 'nome' => 'Menos atraso']);
        $this->createPayment($lessOverdue, ['data' => '2025-03-01', 'referencia' => '2025-03']);

        $moreOverdue = $this->createMember(['numero' => '11', 'nome' => 'Mais atraso']);
        $this->createPayment($moreOverdue, ['data' => '2024-01-01', 'referencia' => '2024-01']);

        $ok = $this->createMember(['numero' => '12', 'nome' => 'Em dia']);
        $this->createPayment($ok, ['data' => '2025-05-01', 'referencia' => '2025-05']);

        QuotaService::clearSituationCache();

        $rows = app(OverdueMembersReportService::class)->rows();

        $this->assertCount(2, $rows);
        $this->assertSame(['Mais atraso', 'Menos atraso'], $rows->pluck('nome')->all());
        $this->assertGreaterThan($rows->last()['dias_atraso'], $rows->first()['dias_atraso']);
    }
}
