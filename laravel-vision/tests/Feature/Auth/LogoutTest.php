<?php

namespace Tests\Feature\Auth;

use Tests\Feature\ApiTestCase;

class LogoutTest extends ApiTestCase
{
    public function test_authenticated_user_can_logout(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/oauth/logout');

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/oauth/logout');

        $response->assertUnauthorized();
    }
}
