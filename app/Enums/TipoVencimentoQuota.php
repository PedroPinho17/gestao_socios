<?php

namespace App\Enums;

enum TipoVencimentoQuota: string
{
    case Aniversario = 'aniversario';
    case DiaFixo = 'dia_fixo';

    public function label(): string
    {
        return match ($this) {
            self::Aniversario => 'Desde último pagamento (aniversário)',
            self::DiaFixo => 'Dia fixo no mês',
        };
    }
}
