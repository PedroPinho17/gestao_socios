<?php

namespace Tests\Unit;

use App\Modules\Members\Services\MemberImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class MemberImportErrorsTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    public function test_rejects_empty_file(): void
    {
        $path = $this->createImportSpreadsheet([]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertTrue($result->hasErrors());
        $this->assertStringContainsString('vazio', $result->errors[0]['message']);

        @unlink($path);
    }

    public function test_rejects_invalid_header(): void
    {
        $path = $this->createImportSpreadsheet([
            ['Coluna A', 'Coluna B'],
            ['1', 'Teste'],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertTrue($result->hasErrors());
        $this->assertStringContainsString('Cabeçalho inválido', $result->errors[0]['message']);

        @unlink($path);
    }

    public function test_requires_member_name_on_first_row(): void
    {
        $plan = $this->createQuotaPlan();

        $path = $this->createImportSpreadsheet([
            $this->importTemplateHeaders(),
            [
                '10', '', '', '', '01/01/2024', $plan->nome,
                '', '', 'Sim', '', '', '', '', '',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->skipped);
        $this->assertStringContainsString('nome é obrigatório', $result->errors[0]['message']);

        @unlink($path);
    }

    public function test_requires_join_date(): void
    {
        $plan = $this->createQuotaPlan();

        $path = $this->createImportSpreadsheet([
            $this->importTemplateHeaders(),
            [
                '11', 'Sem data adesão', '', '', '', $plan->nome,
                '', '', 'Sim', '', '', '', '', '',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->skipped);
        $this->assertStringContainsString('data de adesão', $result->errors[0]['message']);

        @unlink($path);
    }

    public function test_rejects_unknown_quota_plan(): void
    {
        $path = $this->createImportSpreadsheet([
            $this->importTemplateHeaders(),
            [
                '12', 'Plano inexistente', '', '', '01/01/2024', 'Plano que não existe',
                '', '', 'Sim', '', '', '', '', '',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->skipped);
        $this->assertStringContainsString('Plano de quota', $result->errors[0]['message']);

        @unlink($path);
    }

    public function test_rejects_invalid_email(): void
    {
        $plan = $this->createQuotaPlan();

        $path = $this->createImportSpreadsheet([
            $this->importTemplateHeaders(),
            [
                '13', 'Email inválido', 'nao-e-email', '', '01/01/2024', $plan->nome,
                '', '', 'Sim', '', '', '', '', '',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->skipped);
        $this->assertStringContainsString('Email inválido', $result->errors[0]['message']);

        @unlink($path);
    }

    public function test_payment_only_row_requires_existing_member(): void
    {
        $path = $this->createImportSpreadsheet([
            $this->importTemplateHeaders(),
            [
                '999', '', '', '', '', '',
                '', '', '', '', '01/03/2026', '15', '2026-03', 'Março',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->skipped);
        $this->assertStringContainsString('não encontrado', $result->errors[0]['message']);

        @unlink($path);
    }

    public function test_payment_only_row_requires_payment_fields(): void
    {
        $member = $this->createMember(['numero' => '88']);

        $path = $this->createImportSpreadsheet([
            $this->importTemplateHeaders(),
            [
                $member->numero, '', '', '', '', '',
                '', '', '', '', '', '', '', '',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path);

        $this->assertSame(1, $result->skipped);
        $this->assertStringContainsString('campo de pagamento', $result->errors[0]['message']);

        @unlink($path);
    }

    public function test_skips_existing_member_when_updates_disabled(): void
    {
        $plan = $this->createQuotaPlan();
        $member = $this->createMember(['numero' => '55', 'nome' => 'Original']);

        $path = $this->createImportSpreadsheet([
            $this->importTemplateHeaders(),
            [
                $member->numero, 'Nome novo', '', '', '01/01/2024', $plan->nome,
                '', '', 'Sim', '', '', '', '', '',
            ],
        ]);

        $result = app(MemberImportService::class)->import($path, updateExisting: false);

        $this->assertSame(0, $result->updated);
        $this->assertSame(1, $result->skipped);
        $this->assertSame('Original', $member->fresh()->nome);

        @unlink($path);
    }
}
