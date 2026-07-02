<?php

namespace App\Modules\Members\Services;

use App\Modules\Members\Support\MemberCardDimensions;
use App\Modules\Members\Support\MemberCardLayout;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;
use Throwable;

/**
 * PNG via Chrome headless; PDF = 1 página com o PNG (tamanho CR80 + sangria).
 */
class MemberCardBrowsershotExporter
{
    public static function isAvailable(): bool
    {
        return class_exists(Browsershot::class);
    }

    /**
     * PDF profissional: render Chrome → PNG → 1 página PDF (evita A4 / páginas vazias).
     *
     * @param  array<string, mixed>  $data
     */
    public function pdfBytes(array $data): ?string
    {
        $pages = [];

        $front = $this->pngBytes($data, withBleed: true, side: 'front');
        if ($front === null) {
            return null;
        }

        $pages[] = $front;

        if (MemberCardLayout::hasVerso($data['layout'])) {
            $verso = $this->pngBytes($data, withBleed: true, side: 'back');
            if ($verso !== null) {
                $pages[] = $verso;
            }
        }

        return count($pages) === 1
            ? $this->wrapPngAsSinglePagePdf($pages[0], withBleed: true)
            : $this->wrapPngsAsPdf($pages, withBleed: true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function pngBytes(array $data, bool $withBleed = false, string $side = 'front'): ?string
    {
        if (! self::isAvailable()) {
            return null;
        }

        try {
            $data['withBleed'] = $withBleed;
            $data['forExport'] = true;
            $data['cardSide'] = $side;
            $html = view('cards.export.chrome-wrap', $data)->render();

            $width = MemberCardDimensions::widthPx(MemberCardDimensions::DPI, $withBleed);
            $height = MemberCardDimensions::heightPx(MemberCardDimensions::DPI, $withBleed);

            return $this->configure(Browsershot::html($html))
                ->showBackground()
                ->windowSize($width, $height)
                ->deviceScaleFactor(1)
                ->waitUntilNetworkIdle()
                ->screenshot();
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function wrapPngAsSinglePagePdf(string $png, bool $withBleed): string
    {
        [$widthPt, $heightPt] = MemberCardDimensions::paperPoints($withBleed);

        $html = view('cards.export.pdf-from-png', [
            'imageUri' => 'data:image/png;base64,'.base64_encode($png),
            'widthPt' => $widthPt,
            'heightPt' => $heightPt,
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper([0, 0, $widthPt, $heightPt])
            ->output();
    }

    /**
     * @param  list<string>  $pngPages
     */
    private function wrapPngsAsPdf(array $pngPages, bool $withBleed): string
    {
        [$widthPt, $heightPt] = MemberCardDimensions::paperPoints($withBleed);

        $html = view('cards.export.pdf-from-png-pages', [
            'pages' => $pngPages,
            'widthPt' => $widthPt,
            'heightPt' => $heightPt,
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper([0, 0, $widthPt, $heightPt])
            ->output();
    }

    private function configure(Browsershot $browsershot): Browsershot
    {
        $config = config('member-card.export', []);

        if (filled($config['node_binary'] ?? null)) {
            $browsershot->setNodeBinary($config['node_binary']);
        }

        if (filled($config['npm_binary'] ?? null)) {
            $browsershot->setNpmBinary($config['npm_binary']);
        }

        if (filled($config['node_modules_path'] ?? null)) {
            $browsershot->setNodeModulePath($config['node_modules_path']);
        }

        if (filled($config['chrome_path'] ?? null)) {
            $browsershot->setChromePath($config['chrome_path']);
        }

        if ($config['no_sandbox'] ?? false) {
            $browsershot->noSandbox();
        }

        return $browsershot;
    }
}
