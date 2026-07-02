<?php

namespace App\Support;

use App\Models\ClubSetting;
use App\Support\ModuleRegistry;
use App\Support\WebauthnSettings;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

final class ClubBranding
{
    public static function logoRelativePath(): string
    {
        return ltrim((string) config('club.logo', 'img/vale_logo.png'), '/');
    }

    public static function logoPublicPath(): string
    {
        return public_path(self::logoRelativePath());
    }

    public static function hasLogo(): bool
    {
        return is_readable(self::logoPublicPath());
    }

    public static function logoUrl(): ?string
    {
        return self::publicLogoUrl();
    }

    /**
     * URL acessível sem autenticação (login, validação QR, área do sócio).
     */
    public static function publicLogoUrl(): ?string
    {
        if (self::hasLogo()) {
            return asset(self::logoRelativePath());
        }

        $path = self::settings()->logo_path;

        if (blank($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        return route('club.branding.logo');
    }

    public static function logoDataUri(): ?string
    {
        if (self::hasLogo()) {
            $path = self::logoPublicPath();
            $contents = file_get_contents($path);

            if ($contents === false) {
                return self::uploadedLogoDataUri();
            }

            $mime = mime_content_type($path) ?: 'image/png';

            return 'data:'.$mime.';base64,'.base64_encode($contents);
        }

        return self::uploadedLogoDataUri();
    }

    public static function settings(): ClubSetting
    {
        return ClubSetting::current();
    }

    public static function clubName(): string
    {
        return self::settings()->nome_clube ?? 'O meu clube';
    }

    public static function primaryColor(): string
    {
        return self::settings()->panel_primary_color ?? '#10b981';
    }

    public static function gradientFrom(): string
    {
        return self::settings()->card_gradient_from ?? '#0f766e';
    }

    public static function gradientTo(): string
    {
        return self::settings()->card_gradient_to ?? '#0f172a';
    }

    public static function accentColor(): string
    {
        return self::settings()->card_accent_color ?? '#d1fae5';
    }

    /**
     * @return array{
     *     club_name: string,
     *     logo_url: ?string,
     *     primary_color: string,
     *     gradient_from: string,
     *     gradient_to: string,
     *     accent_color: string,
     *     member_area_title: string,
     *     member_area_login_subtitle: string,
     *     modules: array<string, bool>,
     *     member_area_disabled_message: string,
     *     passkeys_enabled: bool,
     * }
     */
    public static function forMemberArea(): array
    {
        return [
            'club_name' => self::clubName(),
            'logo_url' => self::publicLogoUrl(),
            'primary_color' => self::primaryColor(),
            'gradient_from' => self::gradientFrom(),
            'gradient_to' => self::gradientTo(),
            'accent_color' => self::accentColor(),
            'member_area_title' => (string) config('club.member_area.title', 'Área do sócio'),
            'member_area_login_subtitle' => (string) config(
                'club.member_area.login_subtitle',
                'Inicie sessão com o email e password do clube.',
            ),
            'modules' => ModuleRegistry::activeMap(),
            'member_area_disabled_message' => ModuleRegistry::disabledMessage(ModuleRegistry::AREA_SOCIO),
            'passkeys_enabled' => WebauthnSettings::enabled(),
        ];
    }

    public static function brandLockupHtml(): ?Htmlable
    {
        $url = self::logoUrl();

        if ($url === null) {
            return null;
        }

        $name = e(self::clubName());

        return new HtmlString(
            '<span class="fi-brand-lockup" style="display:inline-flex;align-items:center;gap:0.625rem;min-width:0">'.
            '<img src="'.e($url).'" alt="" style="height:2.5rem;width:auto;max-width:3.5rem;object-fit:contain;flex-shrink:0">'.
            '<span class="fi-brand-name" style="font-weight:600;font-size:1rem;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'.
            $name.
            '</span>'.
            '</span>'
        );
    }

    private static function uploadedLogoDataUri(): ?string
    {
        $path = self::settings()->logo_path;

        if (blank($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('local')->get($path);
        $mime = Storage::disk('local')->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
