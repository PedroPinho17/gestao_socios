<?php

namespace Tests\Concerns;

use App\Models\Module;
use App\Modules\Auth\Models\Permissao;
use App\Modules\Auth\Models\User;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Models\QuotaPlan;
use App\Modules\Members\Support\MemberImportColumnMap;
use App\Modules\Payments\Models\Payment;
use App\Support\ModuleRegistry;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait CreatesClubFixtures
{
    protected function createQuotaPlan(array $attributes = []): QuotaPlan
    {
        return QuotaPlan::query()->create(array_merge([
            'nome' => 'Quota mensal test',
            'periodicidade_id' => 1,
            'valor' => 15,
            'tipo_vencimento_quota_id' => 1,
            'dia_vencimento_mes' => 1,
        ], $attributes));
    }

    protected function createMember(array $attributes = []): Member
    {
        if (! array_key_exists('quota_plan_id', $attributes)) {
            $attributes['quota_plan_id'] = $this->createQuotaPlan()->id;
        }

        return Member::query()->create(array_merge([
            'numero' => (string) random_int(1000, 9999),
            'nome' => 'Sócio Teste',
            'email' => 'socio'.random_int(1000, 9999).'@test.pt',
            'data_adesao' => '2024-01-01',
            'ativo' => true,
        ], $attributes));
    }

    protected function createPayment(Member $member, array $attributes = []): Payment
    {
        return Payment::query()->create(array_merge([
            'member_id' => $member->id,
            'data' => '2025-01-01',
            'valor' => 15,
            'referencia' => '2025-01',
        ], $attributes));
    }

    protected function createMemberUser(Member $member, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => $member->nome,
            'email' => $member->email ?? 'socio'.$member->id.'@test.pt',
            'password' => 'password',
            'member_id' => $member->id,
            'permissao_id' => null,
            'must_change_password' => false,
        ], $attributes));
    }

    protected function createStaffUser(int $permissaoId = Permissao::ADMINISTRADOR): User
    {
        return User::factory()->create([
            'name' => 'Staff User',
            'member_id' => null,
            'permissao_id' => $permissaoId,
        ]);
    }

    protected function memberApiToken(User $user): string
    {
        return $user->createToken('member-api')->plainTextToken;
    }

    protected function setModuleEnabled(string $slug, bool $enabled): void
    {
        Module::query()->where('slug', $slug)->update(['enabled' => $enabled]);
        ModuleRegistry::clearCache();
    }

    /**
     * @return list<string>
     */
    protected function importTemplateHeaders(): array
    {
        return MemberImportColumnMap::templateHeaders();
    }

    /**
     * @param  list<list<string>>  $rows
     */
    protected function createImportSpreadsheet(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1');

        $path = storage_path('app/testing_import_'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }
}
