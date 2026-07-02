<?php

namespace App\Modules\Members\Support;

use App\Modules\Members\Models\Member;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\URL;
use Throwable;

class MemberCardQrCode
{
    /**
     * @param  array<string, mixed>  $layout
     */
    public static function payload(Member $member, array $layout): string
    {
        $numero = (string) ($layout['numero_prefix'] ?? '').$member->numero;

        return match ($layout['qr_content'] ?? 'validacao') {
            'numero' => $numero,
            'dados' => json_encode([
                'clube' => $layout['nome_clube'] ?? '',
                'numero' => $numero,
                'nome' => $member->nome,
            ], JSON_UNESCAPED_UNICODE) ?: $numero,
            default => self::validationUrl($member),
        };
    }

    public static function validationUrl(Member $member): string
    {
        if (! $member->exists || $member->id === null) {
            return rtrim((string) config('app.url'), '/').'/validar/exemplo';
        }

        return URL::temporarySignedRoute(
            'member.validate',
            now()->addMonths(max(1, (int) config('member-card.validation_link_ttl_months', 12))),
            ['member' => $member],
            absolute: true,
        );
    }

    public static function dataUri(string $payload): ?string
    {
        if (blank($payload) || ! class_exists(QRCode::class)) {
            return null;
        }

        try {
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'scale' => 3,
                'imageBase64' => true,
                'eccLevel' => QRCode::ECC_M,
            ]);

            return (new QRCode($options))->render($payload);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $layout
     */
    public static function forMember(Member $member, array $layout): ?string
    {
        $showQr = ($layout['show_qr_verso'] ?? false) || ($layout['template'] ?? '') === 'crc_vale';

        if (! $showQr) {
            return null;
        }

        return self::dataUri(self::payload($member, $layout));
    }

    /**
     * @return array<string, string>
     */
    public static function contentOptions(): array
    {
        return [
            'validacao' => 'Link de validação online',
            'numero' => 'Apenas número de sócio',
            'dados' => 'JSON (clube, número, nome)',
        ];
    }
}
