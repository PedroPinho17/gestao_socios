<?php

namespace App\Modules\Members\Services;

use App\Modules\Members\DTOs\MemberImportResult;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Models\QuotaPlan;
use App\Modules\Members\Support\MemberImportColumnMap;
use App\Modules\Members\Support\MemberSpreadsheetWriter;
use App\Modules\Payments\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberImportService
{
    /**
     * @var array<string, Member>
     */
    protected array $membersInSession = [];

    public function import(string $filePath, bool $updateExisting = true): MemberImportResult
    {
        $this->membersInSession = [];

        $rows = $this->readRows($filePath);

        if ($rows === []) {
            return new MemberImportResult(errors: [
                ['row' => 1, 'message' => 'O ficheiro está vazio ou não tem linhas de dados.'],
            ]);
        }

        $headerRow = array_shift($rows);
        $columnMap = MemberImportColumnMap::mapHeaderIndexes($headerRow);

        if (! in_array('nome', $columnMap, true) && ! in_array('numero', $columnMap, true)) {
            return new MemberImportResult(errors: [
                ['row' => 1, 'message' => 'Cabeçalho inválido: falta a coluna «Nome» ou «Número». Use o modelo Excel disponível no backoffice.'],
            ]);
        }

        $result = new MemberImportResult;

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 2;
            $data = $this->extractRowData($row, $columnMap);

            if ($this->rowIsEmpty($data)) {
                continue;
            }

            if ($this->isPaymentOnlyRow($data)) {
                $this->importPaymentOnlyRow($data, $excelRow, $result);

                continue;
            }

            $nome = trim((string) ($data['nome'] ?? ''));

            if ($nome === '') {
                $result->errors[] = [
                    'row' => $excelRow,
                    'message' => 'O nome é obrigatório na primeira linha de cada sócio.',
                ];
                $result->skipped++;

                continue;
            }

            try {
                $memberPayload = $this->buildMemberPayload($data);
            } catch (\InvalidArgumentException $e) {
                $result->errors[] = ['row' => $excelRow, 'message' => $e->getMessage()];
                $result->skipped++;

                continue;
            }

            $numero = trim((string) ($memberPayload['numero'] ?? ''));
            $existing = $numero !== ''
                ? ($this->membersInSession[$numero] ?? Member::query()->where('numero', $numero)->first())
                : null;

            if ($existing && ! $updateExisting) {
                $result->skipped++;

                continue;
            }

            try {
                DB::transaction(function () use ($existing, $memberPayload, $data, &$result): void {
                    if ($existing) {
                        $existing->update($memberPayload);
                        $member = $existing->fresh();
                        $result->updated++;
                    } else {
                        if (blank($memberPayload['numero'] ?? null)) {
                            $memberPayload['numero'] = Member::nextNumero();
                        }

                        $member = Member::query()->create($memberPayload);
                        $result->created++;
                    }

                    $this->rememberMember($member);
                    $this->importPaymentForMember($member, $data, $result);
                });
            } catch (\Throwable $e) {
                $result->errors[] = [
                    'row' => $excelRow,
                    'message' => $e->getMessage(),
                ];
                $result->skipped++;
            }
        }

        return $result;
    }

    public function templateDownloadResponse(): StreamedResponse
    {
        $rows = [
            MemberImportColumnMap::templateHeaders(),
            ...MemberImportColumnMap::templateExampleRows(),
        ];

        return MemberSpreadsheetWriter::downloadResponse($rows, 'modelo_importacao_socios.xlsx');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function importPaymentOnlyRow(array $data, int $excelRow, MemberImportResult $result): void
    {
        $numero = trim((string) ($data['numero'] ?? ''));

        if ($numero === '') {
            $result->errors[] = [
                'row' => $excelRow,
                'message' => 'Linha de pagamento extra: o número de sócio é obrigatório.',
            ];
            $result->skipped++;

            return;
        }

        if (! $this->hasPaymentData($data)) {
            $result->errors[] = [
                'row' => $excelRow,
                'message' => 'Linha com número repetido sem dados de sócio: indique pelo menos um campo de pagamento.',
            ];
            $result->skipped++;

            return;
        }

        $member = $this->membersInSession[$numero]
            ?? Member::query()->where('numero', $numero)->first();

        if (! $member) {
            $result->errors[] = [
                'row' => $excelRow,
                'message' => "Sócio n.º «{$numero}» não encontrado. A primeira linha desse sócio deve ter o nome e os dados completos.",
            ];
            $result->skipped++;

            return;
        }

        try {
            DB::transaction(function () use ($member, $data, &$result): void {
                $this->rememberMember($member);
                $this->importPaymentForMember($member, $data, $result);
            });
        } catch (\Throwable $e) {
            $result->errors[] = [
                'row' => $excelRow,
                'message' => $e->getMessage(),
            ];
            $result->skipped++;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function importPaymentForMember(Member $member, array $data, MemberImportResult $result): void
    {
        if (! $this->hasPaymentData($data)) {
            return;
        }

        $paymentPayload = $this->buildPaymentPayload($data);

        Payment::query()->updateOrCreate(
            [
                'member_id' => $member->id,
                'referencia' => $paymentPayload['referencia'],
            ],
            [
                'data' => $paymentPayload['data'],
                'valor' => $paymentPayload['valor'],
                'notas' => $paymentPayload['notas'],
            ],
        );

        $result->payments++;
    }

    protected function rememberMember(Member $member): void
    {
        $this->membersInSession[$member->numero] = $member;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function isPaymentOnlyRow(array $data): bool
    {
        $numero = trim((string) ($data['numero'] ?? ''));

        if ($numero === '') {
            return false;
        }

        if (trim((string) ($data['nome'] ?? '')) !== '') {
            return false;
        }

        foreach (MemberImportColumnMap::memberFields() as $field) {
            if ($field === 'nome') {
                continue;
            }

            if ($this->fieldHasValue($data[$field] ?? null)) {
                return false;
            }
        }

        return true;
    }

    protected function fieldHasValue(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    /**
     * @return list<list<mixed>>
     */
    protected function readRows(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        return array_values(array_filter(
            $rows,
            fn (array $row): bool => $this->rowHasContent($row),
        ));
    }

    /**
     * @param  list<mixed>  $row
     */
    protected function rowHasContent(array $row): bool
    {
        foreach ($row as $value) {
            if (is_string($value) && trim($value) !== '') {
                return true;
            }

            if (is_numeric($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<mixed>  $row
     * @param  array<int, string>  $columnMap
     * @return array<string, mixed>
     */
    protected function extractRowData(array $row, array $columnMap): array
    {
        $data = [];

        foreach ($columnMap as $index => $field) {
            $data[$field] = $row[$index] ?? null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function rowIsEmpty(array $data): bool
    {
        foreach ($data as $value) {
            if (is_string($value) && trim($value) !== '') {
                return false;
            }

            if (is_numeric($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function buildMemberPayload(array $data): array
    {
        $dataAdesao = $this->parseDate($data['data_adesao'] ?? null);

        if ($dataAdesao === null) {
            throw new \InvalidArgumentException('A data de adesão é obrigatória (formato dd/mm/aaaa).');
        }

        $payload = [
            'numero' => $this->nullableString($data['numero'] ?? null),
            'nome' => trim((string) $data['nome']),
            'email' => $this->nullableString($data['email'] ?? null),
            'telefone' => $this->nullableString($data['telefone'] ?? null),
            'data_adesao' => $dataAdesao->toDateString(),
            'cargo_cartao' => $this->nullableString($data['cargo_cartao'] ?? null),
            'notas' => $this->nullableString($data['notas'] ?? null),
            'ativo' => $this->parseBoolean($data['ativo'] ?? null, default: true),
        ];

        $validade = $this->parseDate($data['validade_manual'] ?? null);

        if ($validade !== null) {
            $payload['validade_manual'] = $validade->toDateString();
        } elseif (array_key_exists('validade_manual', $data) && $this->cellWasExplicitlyEmpty($data['validade_manual'])) {
            $payload['validade_manual'] = null;
        }

        $planName = $this->nullableString($data['quota_plan'] ?? null);

        if ($planName !== null) {
            $plan = QuotaPlan::query()
                ->get()
                ->first(fn (QuotaPlan $plan): bool => strcasecmp(trim($plan->nome), $planName) === 0);

            if (! $plan) {
                throw new \InvalidArgumentException("Plano de quota «{$planName}» não encontrado.");
            }

            $payload['quota_plan_id'] = $plan->id;
        } elseif (array_key_exists('quota_plan', $data) && $this->cellWasExplicitlyEmpty($data['quota_plan'])) {
            $payload['quota_plan_id'] = null;
        }

        if (filled($payload['email'] ?? null) && ! filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email inválido: «{$payload['email']}».");
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hasPaymentData(array $data): bool
    {
        return filled($data['pagamento_data'] ?? null)
            || filled($data['pagamento_valor'] ?? null)
            || filled($data['pagamento_referencia'] ?? null)
            || filled($data['pagamento_notas'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{data: string, valor: string, referencia: string, notas: ?string}
     */
    protected function buildPaymentPayload(array $data): array
    {
        $dataPagamento = $this->parseDate($data['pagamento_data'] ?? null);

        if ($dataPagamento === null) {
            throw new \InvalidArgumentException('Para registar pagamento, indique a data do pagamento.');
        }

        $valor = $this->parseDecimal($data['pagamento_valor'] ?? null);

        if ($valor === null || $valor <= 0) {
            throw new \InvalidArgumentException('Para registar pagamento, indique um valor maior que zero.');
        }

        $referencia = $this->nullableString($data['pagamento_referencia'] ?? null)
            ?? $dataPagamento->format('Y-m');

        return [
            'data' => $dataPagamento->toDateString(),
            'valor' => number_format($valor, 2, '.', ''),
            'referencia' => $referencia,
            'notas' => $this->nullableString($data['pagamento_notas'] ?? null),
        ];
    }

    protected function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (\Throwable) {
                return null;
            }
        }

        $string = trim((string) $value);

        if ($string === '') {
            return null;
        }

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $string);

                if ($parsed !== false) {
                    return $parsed->startOfDay();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($string)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function parseBoolean(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'sim', 's', 'yes', 'y', 'true', 'ativo', 'verdadeiro'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'nao', 'não', 'n', 'no', 'false', 'inativo', 'falso'], true)) {
            return false;
        }

        return $default;
    }

    protected function parseDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace([' ', '€'], '', trim((string) $value));
        $normalized = str_replace(',', '.', $normalized);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    protected function cellWasExplicitlyEmpty(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }
}
