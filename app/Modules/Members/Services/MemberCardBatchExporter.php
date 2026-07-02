<?php

namespace App\Modules\Members\Services;

use App\Modules\Members\Models\Member;
use App\Modules\Members\Support\MemberCardLayout;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class MemberCardBatchExporter
{
    public function __construct(
        private readonly MemberCardViewData $viewData,
        private readonly MemberCardBrowsershotExporter $browsershotExporter,
        private readonly MemberCardGdExporter $gdExporter,
    ) {}

    public function zipActiveMembersResponse(): StreamedResponse
    {
        $filename = 'cartoes_socios_'.now()->format('Y-m-d').'.zip';

        return response()->streamDownload(function (): void {
            $tmp = tempnam(sys_get_temp_dir(), 'cartoes_');
            if ($tmp === false) {
                throw new \RuntimeException('Não foi possível criar ficheiro temporário.');
            }

            $zipPath = $tmp.'.zip';
            @unlink($tmp);

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Não foi possível criar o ZIP.');
            }

            foreach ($this->activeMembers() as $member) {
                $this->addMemberCardsToZip($zip, $member);
            }

            $zip->close();

            readfile($zipPath);
            @unlink($zipPath);
        }, $filename, [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * @return Collection<int, Member>
     */
    private function activeMembers(): Collection
    {
        return Member::query()
            ->with(['quotaPlan.periodicidade', 'payments'])
            ->where('ativo', true)
            ->orderBy('numero')
            ->get();
    }

    private function addMemberCardsToZip(ZipArchive $zip, Member $member): void
    {
        $data = $this->viewData->for($member, forExport: true, withBleed: false);
        $base = $this->safeBaseName($member);

        $front = $this->renderPng($data, 'front');
        if ($front !== null) {
            $zip->addFromString("{$base}_frente.png", $front);
        }

        if (MemberCardLayout::hasVerso($data['layout'])) {
            $verso = $this->renderPng($data, 'back');
            if ($verso !== null) {
                $zip->addFromString("{$base}_verso.png", $verso);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function renderPng(array $data, string $side): ?string
    {
        return $this->browsershotExporter->pngBytes($data, withBleed: false, side: $side)
            ?? ($side === 'front' ? $this->gdExporter->render($data) : $this->gdExporter->renderVerso($data));
    }

    private function safeBaseName(Member $member): string
    {
        $nome = preg_replace('/[^\w\-]+/u', '_', $member->nome) ?: 'socio';

        return 'cartao_'.$member->numero.'_'.$nome;
    }
}
