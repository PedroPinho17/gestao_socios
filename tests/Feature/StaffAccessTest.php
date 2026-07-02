<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClubFixtures;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use CreatesClubFixtures;
    use RefreshDatabase;

    public function test_staff_can_download_member_import_template(): void
    {
        $staff = $this->createStaffUser();

        $this->actingAs($staff)
            ->get(route('members.import.template'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_member_user_cannot_access_staff_export_route(): void
    {
        $member = $this->createMember();
        $user = $this->createMemberUser($member);

        $this->actingAs($user)
            ->get(route('members.export.excel'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_staff_export_route(): void
    {
        $this->get(route('members.export.excel'))
            ->assertRedirect();
    }
}
