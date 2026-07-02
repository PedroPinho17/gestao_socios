<?php

namespace App\Modules\Members\Support;

use App\Modules\Members\Models\Member;
use App\Modules\Payments\Models\Payment;
use Illuminate\Support\Str;

class MemberImportColumnMap
{
    /**
     * @return list<string>
     */
    public static function templateHeaders(): array
    {
        return [
            'Número',
            'Nome',
            'Email',
            'Telefone',
            'Data de adesão',
            'Plano de quota',
            'Texto extra no cartão',
            'Validade no cartão',
            'Ativo',
            'Notas',
            'Pagamento data',
            'Pagamento valor',
            'Pagamento referência',
            'Pagamento notas',
        ];
    }

    /**
     * @return list<list<string>>
     */
    public static function templateExampleRows(): array
    {
        return [
            [
                '1',
                'João Exemplo',
                'joao@clube.pt',
                '912345678',
                '15/01/2025',
                'Quota social — mensal',
                'Equipa sénior',
                '',
                'Sim',
                'Importado via Excel',
                '01/01/2026',
                '15',
                '2026-01',
                'Quota de janeiro',
            ],
            [
                '1',
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
                'Quota de fevereiro',
            ],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function aliases(): array
    {
        return [
            'numero' => ['numero', 'número', 'n.º', 'nº', 'num', 'numero de socio', 'número de sócio'],
            'nome' => ['nome', 'nome completo'],
            'email' => ['email', 'e-mail'],
            'telefone' => ['telefone', 'tel', 'telemovel', 'telemóvel'],
            'data_adesao' => ['data de adesao', 'data de adesão', 'data adesao', 'data adesão', 'adesao', 'adesão'],
            'quota_plan' => ['plano de quota', 'plano', 'quota', 'tipo de pagamento', 'tipo pagamento'],
            'cargo_cartao' => ['texto extra no cartao', 'texto extra no cartão', 'cargo', 'cargo cartao', 'cargo cartão'],
            'validade_manual' => ['validade no cartao', 'validade no cartão', 'validade', 'validade manual'],
            'ativo' => ['ativo', 'estado', 'socio ativo', 'sócio ativo'],
            'notas' => ['notas', 'notas internas'],
            'pagamento_data' => ['pagamento data', 'data pagamento', 'pagamento - data'],
            'pagamento_valor' => ['pagamento valor', 'valor pagamento', 'pagamento - valor', 'valor'],
            'pagamento_referencia' => ['pagamento referencia', 'pagamento referência', 'referencia pagamento', 'referência pagamento', 'pagamento - referencia', 'pagamento - referência', 'referencia', 'referência'],
            'pagamento_notas' => ['pagamento notas', 'pagamento - notas'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function memberFields(): array
    {
        return [
            'nome',
            'email',
            'telefone',
            'data_adesao',
            'quota_plan',
            'cargo_cartao',
            'validade_manual',
            'ativo',
            'notas',
        ];
    }

    /**
     * @param  list<mixed>  $headerRow
     * @return array<int, string>
     */
    public static function mapHeaderIndexes(array $headerRow): array
    {
        $normalizedAliases = [];

        foreach (self::aliases() as $field => $labels) {
            foreach ($labels as $label) {
                $normalizedAliases[self::normalizeHeader($label)] = $field;
            }
        }

        $map = [];

        foreach ($headerRow as $index => $header) {
            if (! is_string($header) && ! is_numeric($header)) {
                continue;
            }

            $normalized = self::normalizeHeader((string) $header);

            if ($normalized === '') {
                continue;
            }

            if (isset($normalizedAliases[$normalized])) {
                $map[$index] = $normalizedAliases[$normalized];
            }
        }

        return $map;
    }

    public static function normalizeHeader(string $header): string
    {
        $header = Str::ascii(trim($header));

        return preg_replace('/\s+/', ' ', strtolower($header)) ?? '';
    }

    /**
     * @return list<string>
     */
    public static function rowFromMember(Member $member, ?Payment $payment = null, bool $includeMemberData = true): array
    {
        $row = array_fill(0, count(self::templateHeaders()), '');

        if ($includeMemberData) {
            $row[0] = $member->numero;
            $row[1] = $member->nome;
            $row[2] = $member->email ?? '';
            $row[3] = $member->telefone ?? '';
            $row[4] = $member->data_adesao?->format('d/m/Y') ?? '';
            $row[5] = $member->quotaPlan?->nome ?? '';
            $row[6] = $member->cargo_cartao ?? '';
            $row[7] = $member->validade_manual?->format('d/m/Y') ?? '';
            $row[8] = $member->ativo ? 'Sim' : 'Não';
            $row[9] = $member->notas ?? '';
        } else {
            $row[0] = $member->numero;
        }

        if ($payment !== null) {
            $row[10] = $payment->data->format('d/m/Y');
            $row[11] = number_format((float) $payment->valor, 2, ',', '');
            $row[12] = $payment->referencia;
            $row[13] = $payment->notas ?? '';
        }

        return $row;
    }
}
