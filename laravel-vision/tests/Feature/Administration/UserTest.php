<?php

namespace Tests\Feature\Administration;

use Auth\Models\User;
use Tests\Feature\ApiTestCase;

class UserTest extends ApiTestCase
{
    // ── LIST ──

    public function test_admin_can_list_users(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/administration/users');

        $response->assertOk()
            ->assertJsonStructure(['current_page', 'data']);
    }

    public function test_admin_can_list_users_with_pagination(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/administration/users?page=1&per_page=5');

        $response->assertOk()
            ->assertJsonPath('current_page', 1);
    }

    public function test_admin_can_search_users(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/administration/users?search=admin');

        $response->assertOk();
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/administration/users?role=Administrator');

        $response->assertOk();
    }

    public function test_operator_cannot_list_users(): void
    {
        $this->actingAsOperator();

        $response = $this->getJson('/api/administration/users');

        $response->assertForbidden();
    }

    public function test_unauthenticated_cannot_list_users(): void
    {
        $response = $this->getJson('/api/administration/users');

        $response->assertUnauthorized();
    }

    // ── SHOW ──

    public function test_admin_can_get_user(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson("/api/administration/users/{$this->admin->id}");

        $response->assertOk()
            ->assertJsonPath('id', $this->admin->id)
            ->assertJsonPath('email', 'admin@example.com');
    }

    public function test_get_nonexistent_user_returns_404(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/administration/users/9999');

        $response->assertNotFound();
    }

    // ── CREATE ──

    public function test_admin_can_create_user(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/administration/users', [
            'name' => 'New Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role_name' => 'Operator',
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'New Test User')
            ->assertJsonPath('email', 'newuser@example.com');
    }

    public function test_create_user_fails_with_duplicate_email(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/administration/users', [
            'name' => 'Duplicate',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role_name' => 'Operator',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_user_fails_without_required_fields(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/administration/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role_name']);
    }

    public function test_create_user_fails_with_invalid_role(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/administration/users', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role_name' => 'NonExistentRole',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role_name']);
    }

    public function test_operator_cannot_create_user(): void
    {
        $this->actingAsOperator();

        $response = $this->postJson('/api/administration/users', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role_name' => 'Operator',
        ]);

        $response->assertForbidden();
    }

    // ── UPDATE ──

    public function test_admin_can_update_user(): void
    {
        $this->actingAsAdmin();

        $response = $this->putJson("/api/administration/users/{$this->operator->id}", [
            'name' => 'Updated Operator Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Updated Operator Name');
    }

    // ── DELETE ──

    public function test_admin_can_delete_user(): void
    {
        $this->actingAsAdmin();

        // Create a user via API first, then delete
        $created = $this->postJson('/api/administration/users', [
            'name' => 'Deletable User',
            'email' => 'deletable@example.com',
            'password' => 'password123',
            'role_name' => 'Operator',
        ]);

        $userId = $created->json('id');

        $response = $this->deleteJson("/api/administration/users/{$userId}");

        $response->assertNoContent();
    }

    public function test_admin_cannot_delete_self(): void
    {
        $this->actingAsAdmin();

        $response = $this->deleteJson("/api/administration/users/{$this->admin->id}");

        $response->assertStatus(403);
    }

    // ── AUTH LOGS ──

    public function test_admin_can_view_user_auth_logs(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson("/api/administration/users/{$this->admin->id}/auth-logs");

        $response->assertOk();
    }
}
