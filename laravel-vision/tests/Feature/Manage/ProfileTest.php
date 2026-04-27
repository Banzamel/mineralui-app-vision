<?php

namespace Tests\Feature\Manage;

use Tests\Feature\ApiTestCase;

class ProfileTest extends ApiTestCase
{
    public function test_authenticated_user_can_get_profile(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/manage/me');

        $response->assertOk()
            ->assertJsonPath('email', 'admin@example.com');
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/manage/me');

        $response->assertUnauthorized();
    }

    public function test_user_can_update_profile(): void
    {
        $this->actingAsAdmin();

        $response = $this->putJson('/api/manage/me', [
            'name' => 'Admin Updated Name',
        ]);

        $response->assertOk();
    }
}
