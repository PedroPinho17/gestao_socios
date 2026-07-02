<?php

namespace Tests\Feature\Api;

use App\Support\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class ClubBrandingApiTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    public function test_branding_endpoint_returns_member_area_payload(): void
    {
        $response = $this->getJson('/api/branding');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'club_name',
                'logo_url',
                'primary_color',
                'gradient_from',
                'gradient_to',
                'accent_color',
                'member_area_title',
                'member_area_login_subtitle',
                'modules',
                'member_area_disabled_message',
                'passkeys_enabled',
            ])
            ->assertJsonPath('modules.'.ModuleRegistry::SOCIOS, true);
    }
}
