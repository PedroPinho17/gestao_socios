<?php

namespace Tests\Unit;

use App\Support\Healthcheck;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HealthcheckTest extends TestCase
{
    public function test_ping_skips_when_url_not_configured(): void
    {
        config(['monitoring.healthchecks.quota_reminders' => null]);

        Http::fake();

        Healthcheck::pingQuotaReminders();

        Http::assertNothingSent();
    }

    public function test_ping_calls_healthchecks_url(): void
    {
        config(['monitoring.healthchecks.quota_reminders' => 'https://hc-ping.com/test-uuid']);

        Http::fake([
            'hc-ping.com/*' => Http::response('OK', 200),
        ]);

        Healthcheck::pingQuotaReminders();

        Http::assertSentCount(1);
    }
}
