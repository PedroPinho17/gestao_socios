<?php

namespace App\Enums;

enum QuotaSituationKind: string
{
    case Inativo = 'inativo';
    case SemPlano = 'sem_plano';
    case Ok = 'ok';
    case DueSoon = 'due_soon';
    case Overdue = 'overdue';
}
