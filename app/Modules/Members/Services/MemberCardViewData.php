<?php

namespace App\Modules\Members\Services;

use App\Models\ClubSetting;
use App\Modules\Members\Models\Member;
use App\Modules\Members\Support\MemberCardLayout;
use App\Modules\Members\Support\MemberCardQrCode;
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
     * Pré-visualização de todos os modelos activos (demonstrações / reuniões).
     *
     * @param  array<string, mixed>|null  $settingsData
     * @return list<array{template: string, label: string, data: array<string, mixed>}>
     */
    public function previewGallery(?array $settingsData = null): array
    {
        $settings = ClubSetting::current();
        $baseLayout = MemberCardLayout::resolve($settings, $settingsData);

        if ($settingsData !== null) {
            foreach (['nome_clube', 'card_gradient_from', 'card_gradient_to', 'card_accent_color', 'card_titulo', 'card_campo_extra_label'] as $key) {
                if (array_key_exists($key, $settingsData)) {
                    $settings->{$key} = $settingsData[$key];
                }
            }
            $baseLayout = MemberCardLayout::resolve($settings, $settingsData);
        }

        $member = Member::query()
            ->with(['quotaPlan.periodicidade', 'payments'])
            ->where('ativo', true)
            ->orderBy('id')
            ->first() ?? $this->demoMember();

        $items = [];

        foreach (MemberCardLayout::normalizeAvailableTemplates($baseLayout['available_templates'] ?? null) as $templateKey) {
            $layout = array_merge($baseLayout, ['template' => $templateKey]);
            $items[] = [
                'template' => $templateKey,
                'label' => MemberCardLayout::catalog()[$templateKey]['label'] ?? $templateKey,
                'data' => $this->build($member, $settings, $layout, forExport: false, withBleed: false),
            ];
        }

        return $items;
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

        $showQr = ($layout['show_qr_verso'] ?? false) || ($layout['template'] ?? '') === 'crc_vale';

        return [
            'member' => $member,
            'settings' => $settings,
            'layout' => $layout,
            'vencimentoLinha' => $vencimentoLinha,
            'planoLinha' => $planoLinha,
            'numeroFormatado' => $numeroFormatado,
            'validadePeriodo' => $this->validadePeriodo($member),
            'logoDataUri' => $this->fileToDataUri($settings->logo_path),
            'fotoDataUri' => $this->fileToDataUri($member->foto_path),
            'qrDataUri' => $showQr ? MemberCardQrCode::forMember($member, $layout) : null,
            'forExport' => $forExport,
            'withBleed' => $withBleed,
        ];
    }

    private function validadePeriodo(Member $member): string
    {
        $year = null;

        if ($member->validade_manual) {
            $year = (int) $member->validade_manual->format('Y');
        } elseif ($member->exists) {
            $situation = $this->quotaService->getSituation($member);
            if ($situation['next_due'] !== null) {
                $year = (int) $situation['next_due']->format('Y');
            }
        }

        $year ??= (int) now()->format('Y');

        return $year.'/'.($year + 1);
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
