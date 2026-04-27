<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Artisan;
use Tests\Feature\ApiTestCase;

class LoginTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Passport needs keys and a client for login tests
        Artisan::call('passport:keys', ['--force' => true]);
        Artisan::call('passport:client', [
            '--password' => true,
            '--public' => true,
            '--name' => 'Desktop Password Grant Client',
            '--provider' => 'users',
        ]);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/oauth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'company_id', 'is_active', 'roles', 'permissions'],
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ]);
    }

    public function test_operator_can_login(): void
    {
        $response = $this->postJson('/oauth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'user@example.com');
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->postJson('/oauth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/oauth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_without_email(): void
    {
        $response = $this->postJson('/oauth/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_without_password(): void
    {
        $response = $this->postJson('/oauth/login', [
            'email' => 'admin@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_returns_user_roles_and_permissions(): void
    {
        $response = $this->postJson('/oauth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $user = $response->json('user');

        $this->assertContains('Administrator', $user['roles']);
        $this->assertNotEmpty($user['permissions']);
    }
}
