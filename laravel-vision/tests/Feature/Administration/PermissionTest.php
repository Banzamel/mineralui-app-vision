<?php

namespace Tests\Feature\Administration;

use Tests\Feature\ApiTestCase;

class PermissionTest extends ApiTestCase
{
    public function test_admin_can_list_permissions(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/administration/permissions');

        $response->assertOk();
    }

    public function test_operator_cannot_list_permissions(): void
    {
        $this->actingAsOperator();

        $response = $this->getJson('/api/administration/permissions');

        $response->assertForbidden();
    }

    public function test_unauthenticated_cannot_list_permissions(): void
    {
        $response = $this->getJson('/api/administration/permissions');

        $response->assertUnauthorized();
    }
}
