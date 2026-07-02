<?php

namespace Tests\Unit;

use App\Modules\Members\Models\Member;
use App\Modules\Members\Models\Periodicidade;
use App\Modules\Members\Models\QuotaPlan;
use App\Modules\Members\Services\MemberExportService;
use App\Modules\Members\Support\MemberImportColumnMap;
use App\Modules\Payments\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class MemberExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_exports_members_in_import_compatible_format(): void
    {
        $this->seedLookupTables();

        $plan = QuotaPlan::query()->create([
            'nome' => 'Quota mensal',
            'periodicidade_id' => 1,
            'valor' => 15,
            'tipo_vencimento_quota_id' => 1,
            'dia_vencimento_mes' => 1,
        ]);

        $member = Member::query()->create([
            'numero' => '5',
            'nome' => 'Ana Exportada',
            'email' => 'ana@clube.pt',
            'data_adesao' => '2024-05-10',
            'quota_plan_id' => $plan->id,
            'ativo' => true,
        ]);

        Payment::query()->create([
            'member_id' => $member->id,
            'data' => '2026-01-01',
            'valor' => 15,
            'referencia' => '2026-01',
            'notas' => 'Jan',
        ]);

        Payment::query()->create([
            'member_id' => $member->id,
            'data' => '2026-02-01',
            'valor' => 15,
            'referencia' => '2026-02',
            'notas' => 'Fev',
        ]);

        $response = app(MemberExportService::class)->exportDownloadResponse();
        $path = storage_path('app/testing_export_'.uniqid().'.xlsx');

        ob_start();
        $response->sendContent();
        file_put_contents($path, ob_get_clean() ?: '');

        $rows = IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);

        $this->assertSame(MemberImportColumnMap::templateHeaders(), $rows[0]);
        $this->assertSame('5', $rows[1][0]);
        $this->assertSame('Ana Exportada', $rows[1][1]);
        $this->assertSame('2026-01', $rows[1][12]);
        $this->assertSame('5', $rows[2][0]);
        $this->assertSame('', $rows[2][1]);
        $this->assertSame('2026-02', $rows[2][12]);

        @unlink($path);
    }

    protected function seedLookupTables(): void
    {
        if (Periodicidade::query()->exists()) {
            return;
        }

        $now = now();

        Periodicidade::query()->insert([
            ['id' => 1, 'slug' => 'mensal', 'nome' => 'Mensal', 'meses' => 1, 'ordem' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        TipoVencimentoQuota::query()->insert([
            ['id' => 1, 'slug' => 'aniversario', 'nome' => 'Aniversário', 'ordem' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
