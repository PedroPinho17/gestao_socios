<?php

namespace App\Services;

use App\Models\ClubSetting;
use App\Models\Member;
use Illuminate\Support\Facades\Storage;

class MemberCardViewData
{
    public function __construct(
        private readonly QuotaService $quotaService,
    ) {}

    /**
     * @return array{member: Member, settings: ClubSetting, vencimentoLinha: ?string, logoDataUri: ?string, fotoDataUri: ?string}
     */
    public function for(Member $member): array
    {
        $member->loadMissing(['quotaPlan', 'payments']);
        $settings = ClubSetting::current();

        $vencimentoLinha = null;
        if ($settings->show_proximo_vencimento) {
            if ($member->validade_manual) {
                $vencimentoLinha = 'Válido até '.$this->quotaService->formatDatePT($member->validade_manual);
            } else {
                $situation = $this->quotaService->getSituation($member);
                if (! in_array($situation['kind']->value, ['inativo', 'sem_plano'], true)) {
                    $vencimentoLinha = 'Próximo vencimento: '.$this->quotaService->formatDatePT($situation['next_due']);
                }
            }
        }

        return [
            'member' => $member,
            'settings' => $settings,
            'vencimentoLinha' => $vencimentoLinha,
            'logoDataUri' => $this->fileToDataUri($settings->logo_path),
            'fotoDataUri' => $this->fileToDataUri($member->foto_path),
        ];
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
