<?php

namespace App\Modules\Members\Services;

use App\Modules\Members\Models\Member;
use App\Modules\Members\Support\MemberCardDimensions;
use App\Modules\Members\Support\MemberCardLayout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Throwable;

class MemberCardRenderer
{
    public function __construct(
        private readonly MemberCardViewData $viewData,
        private readonly MemberCardBrowsershotExporter $browsershotExporter,
        private readonly MemberCardGdExporter $gdExporter,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function renderHtml(array $data): string
    {
        $template = $data['layout']['template'] ?? 'classic';

        if (! view()->exists('cards.templates.'.$template)) {
            $template = 'classic';
        }

        return view('cards.templates.'.$template, $data)->render();
    }

    public function pdfResponse(Member $member): Response
    {
        $data = $this->viewData->for($member, forExport: true, withBleed: true);
        $safeName = $this->safeFilename($member->nome);
        $filename = "cartao_{$member->numero}_{$safeName}.pdf";

        $pdf = $this->browsershotExporter->pdfBytes($data);

        if ($pdf !== null) {
            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'no-store',
            ]);
        }

        return $this->dompdfResponse($data, $filename);
    }

    public function pngResponse(Member $member): BaseResponse
    {
        return $this->pngSideResponse($member, 'front');
    }

    public function pngVersoResponse(Member $member): BaseResponse
    {
        $data = $this->viewData->for($member, forExport: true, withBleed: false);

        if (! MemberCardLayout::hasVerso($data['layout'])) {
            abort(404, 'Este cartão não tem verso configurado (texto ou QR).');
        }

        return $this->pngSideResponse($member, 'back');
    }

    private function pngSideResponse(Member $member, string $side): BaseResponse
    {
        $data = $this->viewData->for($member, forExport: true, withBleed: false);
        $safeName = $this->safeFilename($member->nome);
        $suffix = $side === 'back' ? '_verso' : '';
        $filename = "cartao_{$member->numero}_{$safeName}{$suffix}_300dpi.png";

        $png = $this->browsershotExporter->pngBytes($data, side: $side);

        if ($png === null) {
            $png = $side === 'back'
                ? $this->gdExporter->renderVerso($data)
                : $this->renderPngFallback($data);
        }

        if ($png === null) {
            abort(503, 'Exportação PNG indisponível. Instale Node + Chrome (Browsershot) ou active PHP GD.');
        }

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function dompdfResponse(array $data, string $filename): Response
    {
        $html = view('cards.export.dompdf', $data)->render();
        [$widthPt, $heightPt] = MemberCardDimensions::paperPoints(withBleed: true);

        return Pdf::loadHTML($html)
            ->setPaper([0, 0, $widthPt, $heightPt])
            ->download($filename);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function renderPngFallback(array $data): ?string
    {
        $html = view('cards.export.chrome-wrap', array_merge($data, [
            'forExport' => true,
            'withBleed' => false,
            'cardSide' => 'front',
        ]))->render();

        $width = MemberCardDimensions::widthPx();
        $height = MemberCardDimensions::heightPx();

        return $this->pngViaPdfImagick($html, $width, $height)
            ?? $this->gdExporter->render($data);
    }

    private function pngViaPdfImagick(string $html, int $width, int $height): ?string
    {
        if (! extension_loaded('imagick')) {
            return null;
        }

        try {
            [$widthPt, $heightPt] = MemberCardDimensions::paperPoints(withBleed: false);
            $pdfBytes = Pdf::loadHTML($html)
                ->setPaper([0, 0, $widthPt, $heightPt])
                ->output();

            $imagick = new \Imagick;
            $imagick->setResolution(MemberCardDimensions::DPI, MemberCardDimensions::DPI);
            $imagick->readImageBlob($pdfBytes);
            $imagick->setIteratorIndex(0);
            $imagick->setImageFormat('png');
            $imagick->setImageBackgroundColor('white');
            $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);

            if ($imagick->getImageWidth() !== $width || $imagick->getImageHeight() !== $height) {
                $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
            }

            $png = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();

            return $png;
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function safeFilename(string $name): string
    {
        return preg_replace('/[^\w\-]+/u', '_', $name) ?: 'socio';
    }
}
