<?php

namespace App\Services;

use App\Support\MemberCardDimensions;
use Throwable;

/**
 * Gera PNG do cartão via GD (fallback quando Browsershot/Imagick não existem).
 */
class MemberCardGdExporter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function render(array $data): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        try {
            return $this->build($data);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function renderVerso(array $data): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        try {
            return $this->buildVerso($data);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function build(array $data): string
    {
        $layout = $data['layout'];
        $member = $data['member'];
        $width = MemberCardDimensions::widthPx();
        $height = MemberCardDimensions::heightPx();

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new \RuntimeException('GD: não foi possível criar imagem.');
        }

        $bgFrom = $this->hexToRgb($layout['gradient_from'] ?? '#0f766e');
        $bgTo = $this->hexToRgb($layout['gradient_to'] ?? '#0f172a');
        $accent = $this->hexToRgb($layout['accent_color'] ?? '#d1fae5');
        $text = $this->hexToRgb($layout['text_color'] ?? '#ffffff');

        $this->fillVerticalGradient($image, $width, $height, $bgFrom, $bgTo);

        $font = $this->resolveFontPath();
        $textX = (int) round($width * 0.36);
        $y = 44;

        if ($logoImage = $this->imageFromDataUri($data['logoDataUri'] ?? null)) {
            $this->pasteResized($image, $logoImage, 16, (int) round(($height - 70) / 2), 110, 70);
        } elseif ($font) {
            $this->drawText($image, $font, 10, 20, 42, $accent, mb_substr($layout['nome_clube'] ?? '', 0, 16));
        }

        if ($font) {
            $this->drawText($image, $font, 9, $textX, 28, $accent, mb_strtoupper($layout['card_titulo'] ?? 'Sócio'));

            if ($layout['show_nome'] ?? true) {
                $y = 52;
                $this->drawText($image, $font, 14, $textX, $y, $text, $member->nome);
                $y += 26;
            }

            if (($layout['show_cargo'] ?? true) && filled($member->cargo_cartao)) {
                $this->drawText($image, $font, 10, $textX, $y, $accent, ($layout['cargo_label'] ?? 'Cargo').': '.$member->cargo_cartao);
                $y += 18;
            }

            if ($layout['show_numero'] ?? true) {
                $this->drawText($image, $font, 13, $textX, $y, $accent, 'N.º '.($data['numeroFormatado'] ?? $member->numero));
                $y += 22;
            }

            if (! empty($data['planoLinha'])) {
                $this->drawText($image, $font, 10, $textX, $y, $accent, $data['planoLinha']);
                $y += 16;
            }

            if (($layout['show_email'] ?? false) && filled($member->email)) {
                $this->drawText($image, $font, 9, $textX, $y, $accent, $member->email);
                $y += 14;
            }

            if (($layout['show_telefone'] ?? false) && filled($member->telefone)) {
                $this->drawText($image, $font, 9, $textX, $y, $accent, $member->telefone);
                $y += 14;
            }

            if ($layout['show_adesao'] ?? false) {
                $line = 'Adesão: '.$member->data_adesao->format('d/m/Y').($member->ativo ? '' : ' · Inativo');
                $this->drawText($image, $font, 9, $textX, $y, $accent, $line);
                $y += 14;
            }

            if (! empty($data['vencimentoLinha'])) {
                $this->drawText($image, $font, 9, $textX, $y, $accent, $data['vencimentoLinha']);
            }

            if (filled($layout['footer_text'] ?? '')) {
                $this->drawText($image, $font, 8, $textX, $height - 20, $accent, $layout['footer_text']);
            }
        } else {
            $this->drawBuiltinText($image, $text, 18, 40, 'Cartao: '.$member->nome);
        }

        if (($layout['show_foto'] ?? true) && ($photo = $this->imageFromDataUri($data['fotoDataUri'] ?? null))) {
            $this->pasteResized($image, $photo, $width - 118, $height - 118, 100, 100);
        }

        ob_start();
        imagepng($image, null, 6);
        $png = ob_get_clean();
        imagedestroy($image);

        if ($png === false) {
            throw new \RuntimeException('GD: falha ao gerar PNG.');
        }

        return $png;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildVerso(array $data): string
    {
        $layout = $data['layout'];
        $width = MemberCardDimensions::widthPx();
        $height = MemberCardDimensions::heightPx();

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new \RuntimeException('GD: não foi possível criar imagem.');
        }

        $bgFrom = $this->hexToRgb($layout['gradient_to'] ?? '#0f172a');
        $bgTo = $this->hexToRgb($layout['gradient_from'] ?? '#0f766e');
        $accent = $this->hexToRgb($layout['accent_color'] ?? '#d1fae5');

        $this->fillVerticalGradient($image, $width, $height, $bgFrom, $bgTo);

        $font = $this->resolveFontPath();
        $centerX = (int) ($width / 2);

        if ($logoImage = $this->imageFromDataUri($data['logoDataUri'] ?? null)) {
            $this->pasteResized($image, $logoImage, (int) (($width - 110) / 2), 16, 110, 40);
        }

        $textStartY = 70;

        if ($qrImage = $this->imageFromDataUri($data['qrDataUri'] ?? null)) {
            $qrSize = 100;
            $this->pasteResized($image, $qrImage, (int) (($width - $qrSize) / 2), $textStartY, $qrSize, $qrSize);
            $textStartY += $qrSize + 12;
        }

        if ($font) {
            $this->drawCenteredText($image, $font, 9, 52, $accent, mb_strtoupper($layout['nome_clube'] ?? ''));

            if (filled($layout['verso_text'] ?? '')) {
                $this->drawCenteredWrappedText(
                    $image,
                    $font,
                    8,
                    $textStartY,
                    $height - 48,
                    $accent,
                    (string) $layout['verso_text'],
                );
            }

            if ($layout['show_numero'] ?? true) {
                $numero = 'N.º '.($data['numeroFormatado'] ?? '');
                $this->drawCenteredText($image, $font, 11, $height - 36, $accent, $numero);
            }
        } else {
            $this->drawBuiltinText($image, $accent, 18, (int) ($height / 2), 'Verso');
        }

        ob_start();
        imagepng($image, null, 6);
        $png = ob_get_clean();
        imagedestroy($image);

        if ($png === false) {
            throw new \RuntimeException('GD: falha ao gerar PNG.');
        }

        return $png;
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private function drawCenteredText(\GdImage $image, string $font, int $size, int $y, array $rgb, string $text): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        if ($box === false) {
            return;
        }

        $textWidth = abs($box[2] - $box[0]);
        $x = (int) ((imagesx($image) - $textWidth) / 2);
        $this->drawText($image, $font, $size, $x, $y, $rgb, $text);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private function drawCenteredWrappedText(\GdImage $image, string $font, int $size, int $yStart, int $maxY, array $rgb, string $text): void
    {
        $lines = explode("\n", wordwrap($text, 42, "\n", true));
        $y = $yStart;

        foreach ($lines as $line) {
            if ($y > $maxY) {
                break;
            }

            $this->drawCenteredText($image, $font, $size, $y, $rgb, $line);
            $y += $size + 6;
        }
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $from
     * @param  array{0: int, 1: int, 2: int}  $to
     */
    private function fillVerticalGradient(\GdImage $image, int $width, int $height, array $from, array $to): void
    {
        for ($y = 0; $y < $height; $y++) {
            $ratio = $height > 1 ? $y / ($height - 1) : 0;
            $r = (int) ($from[0] + ($to[0] - $from[0]) * $ratio);
            $g = (int) ($from[1] + ($to[1] - $from[1]) * $ratio);
            $b = (int) ($from[2] + ($to[2] - $from[2]) * $ratio);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private function drawBuiltinText(\GdImage $image, array $rgb, int $x, int $y, string $text): void
    {
        $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
        imagestring($image, 3, $x, $y, $text, $color);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private function drawText(\GdImage $image, string $font, int $size, int $x, int $y, array $rgb, string $text): void
    {
        $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    }

    private function resolveFontPath(): ?string
    {
        $candidates = [
            base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf'),
            'C:\\Windows\\Fonts\\arial.ttf',
            'C:\\Windows\\Fonts\\segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function imageFromDataUri(?string $dataUri): ?\GdImage
    {
        if (blank($dataUri) || ! str_starts_with($dataUri, 'data:')) {
            return null;
        }

        $comma = strpos($dataUri, ',');
        if ($comma === false) {
            return null;
        }

        $raw = base64_decode(substr($dataUri, $comma + 1));
        if ($raw === false) {
            return null;
        }

        $image = @imagecreatefromstring($raw);

        return $image instanceof \GdImage ? $image : null;
    }

    private function pasteResized(\GdImage $canvas, \GdImage $source, int $x, int $y, int $w, int $h): void
    {
        $sw = imagesx($source);
        $sh = imagesy($source);
        imagecopyresampled($canvas, $source, $x, $y, 0, 0, $w, $h, $sw, $sh);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
