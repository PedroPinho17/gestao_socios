<?php

namespace App\Modules\Members\DTOs;

class MemberImportResult
{
    /**
     * @param  list<array{row: int, message: string}>  $errors
     */
    public function __construct(
        public int $created = 0,
        public int $updated = 0,
        public int $payments = 0,
        public int $skipped = 0,
        public array $errors = [],
    ) {}

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function processed(): int
    {
        return $this->created + $this->updated;
    }

    public function summary(): string
    {
        $parts = [];

        if ($this->created > 0) {
            $parts[] = "{$this->created} criado(s)";
        }

        if ($this->updated > 0) {
            $parts[] = "{$this->updated} actualizado(s)";
        }

        if ($this->payments > 0) {
            $parts[] = "{$this->payments} pagamento(s)";
        }

        if ($this->skipped > 0) {
            $parts[] = "{$this->skipped} ignorado(s)";
        }

        if ($parts === []) {
            return 'Nenhuma linha importada.';
        }

        return implode(', ', $parts).'.';
    }
}
