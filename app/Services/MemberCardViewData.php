<?php

namespace App\Services;

use App\Models\ClubSetting;
use App\Models\Member;
use App\Support\MemberCardLayout;
use App\Support\MemberCardQrCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MemberCardViewData
{
    public function __construct(
        private readonly QuotaService $quotaService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function for(Member $member, bool $forExport = false, bool $withBleed = false): array
    {
        $member->loadMissing(['quotaPlan.periodicidade', 'payments']);
        $settings = ClubSetting::current();

        return $this->build($member, $settings, MemberCardLayout::resolve($settings), $forExport, $withBleed);
    }

    /**
     * @param  array<string, mixed>  $settingsData  Form state from Filament (optional)
     * @return array<string, mixed>
     */
    public function preview(?array $settingsData = null): array
    {
        $settings = ClubSetting::current();
        $layout = MemberCardLayout::resolve($settings, $settingsData);

        if ($settingsData !== null) {
            foreach (['nome_clube', 'card_gradient_from', 'card_gradient_to', 'card_accent_color', 'card_titulo', 'card_campo_extra_label'] as $key) {
                if (array_key_exists($key, $settingsData)) {
                    $settings->{$key} = $settingsData[$key];
                }
            }
            $layout = MemberCardLayout::resolve($settings, $settingsData);
        }

        $member = Member::query()
            ->with(['quotaPlan.periodicidade', 'payments'])
            ->where('ativo', true)
            ->orderBy('id')
            ->first();

        if (! $member) {
            $member = $this->demoMember();
        }

        return $this->build($member, $settings, $layout, forExport: false, withBleed: false);
    }

    /**
     * @param  array<string, mixed>  $layout
     * @return array<string, mixed>
     */
    private function build(
        Member $member,
        ClubSetting $settings,
        array $layout,
        bool $forExport,
        bool $withBleed,
    ): array {
        $vencimentoLinha = null;
        if ($layout['show_validade']) {
            if ($member->validade_manual) {
                $vencimentoLinha = 'Válido até '.$this->quotaService->formatDatePT($member->validade_manual);
            } elseif ($member->exists) {
                $situation = $this->quotaService->getSituation($member);
                if (! in_array($situation['kind']->value, ['inativo', 'sem_plano'], true)) {
                    $vencimentoLinha = 'Próximo vencimento: '.$this->quotaService->formatDatePT($situation['next_due']);
                }
            } else {
                $vencimentoLinha = 'Próximo vencimento: '.now()->addMonths(3)->format('d/m/Y');
            }
        }

        $planoLinha = null;
        if ($layout['show_plano'] && $member->quotaPlan) {
            $planoLinha = $member->quotaPlan->nome;
        }

        $prefix = (string) ($layout['numero_prefix'] ?? '');
        $numeroFormatado = $prefix.$member->numero;

        return [
            'member' => $member,
            'settings' => $settings,
            'layout' => $layout,
            'vencimentoLinha' => $vencimentoLinha,
            'planoLinha' => $planoLinha,
            'numeroFormatado' => $numeroFormatado,
            'logoDataUri' => $this->fileToDataUri($settings->logo_path),
            'fotoDataUri' => $this->fileToDataUri($member->foto_path),
            'qrDataUri' => MemberCardQrCode::forMember($member, $layout),
            'forExport' => $forExport,
            'withBleed' => $withBleed,
        ];
    }

    private function demoMember(): Member
    {
        $member = new Member([
            'numero' => '1234',
            'nome' => 'Maria Exemplo Silva',
            'email' => 'maria.exemplo@clube.pt',
            'telefone' => '912 345 678',
            'data_adesao' => Carbon::parse('2024-01-15'),
            'ativo' => true,
            'cargo_cartao' => 'Sócia fundadora',
        ]);
        $member->id = 0;

        return $member;
    }

    private function fileToDataUri(?string $path): ?string
    {
        if (blank($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($path);
        $mime = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
