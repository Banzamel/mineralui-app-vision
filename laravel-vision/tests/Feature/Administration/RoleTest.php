<?php

namespace Tests\Feature\Administration;

use Tests\Feature\ApiTestCase;

class RoleTest extends ApiTestCase
{
    // ── LIST ──

    public function test_admin_can_list_roles(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/administration/roles');

        $response->assertOk();
    }

    public function test_operator_cannot_list_roles(): void
    {
        $this->actingAsOperator();

        $response = $this->getJson('/api/administration/roles');

        $response->assertForbidden();
    }

    // ── CREATE ──

    public function test_admin_can_create_role(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/administration/roles', [
            'name' => 'Receptionist',
            'permissions' => ['users.view', 'cameras.view'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'Receptionist');
    }

    public function test_create_role_fails_without_name(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/administration/roles', [
            'permissions' => ['users.view'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // ── UPDATE ──

    public function test_admin_can_update_role(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/administration/roles', [
            'name' => 'TempRole',
            'permissions' => ['users.view'],
        ]);

        $roleId = $createResponse->json('id');

        $response = $this->putJson("/api/administration/roles/{$roleId}", [
            'name' => 'UpdatedRole',
            'permissions' => ['users.view', 'users.create'],
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'UpdatedRole');
    }

    // ── DELETE ──

    public function test_admin_can_delete_role(): void
    {
        $this->actingAsAdmin();

        $createResponse = $this->postJson('/api/administration/roles', [
            'name' => 'DeletableRole',
            'permissions' => ['users.view'],
        ]);

        $roleId = $createResponse->json('id');

        $response = $this->deleteJson("/api/administration/roles/{$roleId}");

        $response->assertNoContent();
    }
}
