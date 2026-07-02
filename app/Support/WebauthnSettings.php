<?php

namespace App\Support;

use App\Models\AppSetting;

final class WebauthnSettings
{
    public static function enabled(): bool
    {
        if (! (bool) config('webauthn.enable', true)) {
            return false;
        }

        return AppSetting::bool(AppSetting::PASSKEYS_ATIVAS, true);
    }

    public static function ensureEnabled(): void
    {
        if (! self::enabled()) {
            abort(404);
        }
    }
}
