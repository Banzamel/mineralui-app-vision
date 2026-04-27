<?php

namespace Tests\Feature\UserScopes;

use Tests\Feature\ApiTestCase;

class UserScopeTest extends ApiTestCase
{
    public function test_admin_can_set_user_scopes(): void
    {
        $this->actingAsAdmin();

        $response = $this->putJson("/api/vision/users/{$this->operator->id}/scopes", [
            'scopes' => [
                ['type' => 'camera', 'scope_id' => '42'],
                ['type' => 'address', 'scope_id' => 'ul. Przykładowa 1'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonCount(2);

        $this->assertDatabaseCount('vision_user_scopes', 2);
    }

    public function test_admin_can_get_user_scopes(): void
    {
        $this->actingAsAdmin();

        $this->putJson("/api/vision/users/{$this->operator->id}/scopes", [
            'scopes' => [
                ['type' => 'camera', 'scope_id' => '1'],
            ],
        ]);

        $response = $this->getJson("/api/vision/users/{$this->operator->id}/scopes");

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.type', 'camera');
    }

    public function test_put_scopes_replaces_old_set(): void
    {
        $this->actingAsAdmin();

        $this->putJson("/api/vision/users/{$this->operator->id}/scopes", [
            'scopes' => [
                ['type' => 'camera', 'scope_id' => '1'],
                ['type' => 'camera', 'scope_id' => '2'],
            ],
        ]);

        $this->putJson("/api/vision/users/{$this->operator->id}/scopes", [
            'scopes' => [
                ['type' => 'building', 'scope_id' => '10'],
            ],
        ]);

        $this->assertDatabaseCount('vision_user_scopes', 1);
    }

    public function test_invalid_scope_type_is_rejected(): void
    {
        $this->actingAsAdmin();

        $response = $this->putJson("/api/vision/users/{$this->operator->id}/scopes", [
            'scopes' => [
                ['type' => 'floor', 'scope_id' => '1'],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_operator_cannot_set_user_scopes(): void
    {
        $this->actingAsOperator();

        $response = $this->putJson("/api/vision/users/{$this->operator->id}/scopes", [
            'scopes' => [],
        ]);

        $response->assertForbidden();
    }
}
