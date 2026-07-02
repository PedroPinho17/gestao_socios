<?php

namespace App\Modules\Members\DTOs;

use Carbon\Carbon;

/**
 * Linha normalizada do Excel de importação de sócios.
 */
readonly class MemberImportRowData
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public int $excelRow,
        public array $raw,
        public ?string $numero = null,
        public ?string $nome = null,
        public ?Carbon $dataAdesao = null,
        public bool $paymentOnly = false,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromParsed(int $excelRow, array $data, bool $paymentOnly): self
    {
        $numero = trim((string) ($data['numero'] ?? ''));
        $nome = trim((string) ($data['nome'] ?? ''));

        return new self(
            excelRow: $excelRow,
            raw: $data,
            numero: $numero !== '' ? $numero : null,
            nome: $nome !== '' ? $nome : null,
            paymentOnly: $paymentOnly,
        );
    }
}
