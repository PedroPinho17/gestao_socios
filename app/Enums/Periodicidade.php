<?php

namespace App\Enums;

enum Periodicidade: string
{
    case Mensal = 'mensal';
    case Trimestral = 'trimestral';
    case Semestral = 'semestral';
    case Anual = 'anual';

    public function label(): string
    {
        return match ($this) {
            self::Mensal => 'Mensal',
            self::Trimestral => 'Trimestral',
            self::Semestral => 'Semestral',
            self::Anual => 'Anual',
        };
    }

    public function periodMonths(): int
    {
        return match ($this) {
            self::Mensal => 1,
            self::Trimestral => 3,
            self::Semestral => 6,
            self::Anual => 12,
        };
    }
}
