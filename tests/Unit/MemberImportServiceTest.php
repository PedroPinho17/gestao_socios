<?php

namespace Tests\Unit;

use App\Modules\Members\Models\Member;
use App\Modules\Members\Models\Periodicidade;
use App\Modules\Members\Models\QuotaPlan;
use App\Modules\Members\Models\TipoVencimentoQuota;
use App\Modules\Members\Services\MemberImportService;
use App\Modules\Members\Support\MemberImportColumnMap;
use App\Modules\Payments\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class MemberImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_member_and_payment_from_excel(): void
    {
        $this->seedLookupTables();
        $plan = QuotaPlan::query()->create([
            'nome' => 'Quota mensal',
            'periodicidade_id' => 1,
            'valor' => 15,
            'tipo_vencimento_quota_id' => 1,
            'dia_vencimento_mes' => 1,
        ]);

        $path = $this->createSpreadsheet([
            MemberImportColumnMap::templateHeaders(),
            [
                '42',
                'Maria Importada',
                'maria@clube.pt',
                '911111111',
                '10/02/2024',
                $plan->nome,
                'Equipa A',
                '31/12/2026',
                'Sim',
                'Nota teste',
                '01/03/2026',
                '15,50',
                '2026-03',
                'Quota março',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->created);
        $this->assertSame(1, $result->payments);
        $this->assertFalse($result->hasErrors());

        $member = Member::query()->where('numero', '42')->first();

        $this->assertNotNull($member);
        $this->assertSame('Maria Importada', $member->nome);
        $this->assertSame($plan->id, $member->quota_plan_id);
        $this->assertTrue($member->ativo);

        $payment = Payment::query()->where('member_id', $member->id)->first();

        $this->assertNotNull($payment);
        $this->assertSame('2026-03', $payment->referencia);
        $this->assertSame('15.50', $payment->valor);

        @unlink($path);
    }

    public function test_updates_existing_member_by_numero(): void
    {
        $this->seedLookupTables();
        $plan = QuotaPlan::query()->create([
            'nome' => 'Quota anual',
            'periodicidade_id' => 4,
            'valor' => 120,
            'tipo_vencimento_quota_id' => 1,
            'dia_vencimento_mes' => 1,
        ]);

        Member::query()->create([
            'numero' => '7',
            'nome' => 'Nome antigo',
            'data_adesao' => '2020-01-01',
            'quota_plan_id' => $plan->id,
            'ativo' => true,
        ]);

        $path = $this->createSpreadsheet([
            MemberImportColumnMap::templateHeaders(),
            [
                '7',
                'Nome actualizado',
                '',
                '',
                '01/01/2020',
                $plan->nome,
                '',
                '',
                'Não',
                '',
                '',
                '',
                '',
                '',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->updated);
        $this->assertSame(0, $result->created);

        $member = Member::query()->where('numero', '7')->first();

        $this->assertSame('Nome actualizado', $member->nome);
        $this->assertFalse($member->ativo);

        @unlink($path);
    }

    public function test_imports_multiple_payments_for_same_member_number(): void
    {
        $this->seedLookupTables();
        $plan = QuotaPlan::query()->create([
            'nome' => 'Quota mensal',
            'periodicidade_id' => 1,
            'valor' => 15,
            'tipo_vencimento_quota_id' => 1,
            'dia_vencimento_mes' => 1,
        ]);

        $path = $this->createSpreadsheet([
            MemberImportColumnMap::templateHeaders(),
            [
                '99',
                'Pedro Vários Pagamentos',
                'pedro@clube.pt',
                '',
                '01/01/2025',
                $plan->nome,
                '',
                '',
                'Sim',
                '',
                '01/01/2026',
                '15',
                '2026-01',
                'Janeiro',
            ],
            [
                '99',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '01/02/2026',
                '15',
                '2026-02',
                'Fevereiro',
            ],
            [
                '99',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '01/03/2026',
                '15',
                '2026-03',
                'Março',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->created);
        $this->assertSame(3, $result->payments);
        $this->assertFalse($result->hasErrors());

        $member = Member::query()->where('numero', '99')->first();

        $this->assertNotNull($member);
        $this->assertSame(3, Payment::query()->where('member_id', $member->id)->count());

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
            ['id' => 4, 'slug' => 'anual', 'nome' => 'Anual', 'meses' => 12, 'ordem' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);

        TipoVencimentoQuota::query()->insert([
            ['id' => 1, 'slug' => 'aniversario', 'nome' => 'Aniversário', 'ordem' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * @param  list<list<string>>  $rows
     */
    protected function createSpreadsheet(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $path = storage_path('app/testing_import_'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }
}
