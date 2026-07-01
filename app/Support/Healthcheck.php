<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class Healthcheck
{
    public static function pingQuotaReminders(): void
    {
        self::ping(config('monitoring.healthchecks.quota_reminders'));
    }

    public static function ping(?string $url): void
    {
        if (blank($url)) {
            return;
        }

        try {
            Http::timeout(10)->get($url)->throw();
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar ping de healthcheck.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
