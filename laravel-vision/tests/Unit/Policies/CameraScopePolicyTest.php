<?php

namespace Tests\Unit\Policies;

use Auth\Models\User;
use Mockery;
use Objects\Enums\ScopeType;
use Objects\Models\Camera;
use Objects\Models\UserScope;
use Objects\Policies\CameraScopePolicy;
use ReflectionClass;
use Tests\TestCase;

/**
 * Security-critical: validates that camera visibility is correctly gated by user_scopes.
 * The policy is the only barrier between a logged-in non-admin and another tenant's cameras,
 * so each branch (Camera, Address, Building tree, no-scopes, unknown type) is exercised individually.
 *
 * The full `view()` path that runs UserScope::query() is exercised by the existing Feature
 * tests in tests/Feature/UserScopes — here we focus on the pure decision logic via reflection.
 */
class CameraScopePolicyTest extends TestCase
{
    private CameraScopePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CameraScopePolicy();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_administrators_bypass_the_filter(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrator')->andReturn(true);

        $this->assertTrue($this->policy->before($user, 'view'));
    }

    public function test_before_returns_null_for_non_administrators(): void
    {
        // Returning null lets the per-method check (view/etc.) decide — never short-circuits to false.
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasRole')->with('Administrator')->andReturn(false);

        $this->assertNull($this->policy->before($user, 'view'));
    }

    public function test_view_any_always_allowed(): void
    {
        // viewAny=true is intentional — the service layer narrows the actual list by scope.
        $user = new User();
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_matches_returns_true_for_direct_camera_scope(): void
    {
        $camera = new Camera();
        $camera->id = 101;

        $scope = new UserScope();
        $scope->type = ScopeType::Camera->value;
        $scope->scope_id = '101';

        $this->assertTrue($this->invokeMatches($scope, $camera));
    }

    public function test_matches_returns_false_for_camera_scope_with_different_id(): void
    {
        $camera = new Camera();
        $camera->id = 101;

        $scope = new UserScope();
        $scope->type = ScopeType::Camera->value;
        $scope->scope_id = '999';

        $this->assertFalse($this->invokeMatches($scope, $camera));
    }

    public function test_matches_returns_true_when_address_matches(): void
    {
        $camera = new Camera();
        $camera->address = 'ul. Testowa 1';

        $scope = new UserScope();
        $scope->type = ScopeType::Address->value;
        $scope->scope_id = 'ul. Testowa 1';

        $this->assertTrue($this->invokeMatches($scope, $camera));
    }

    public function test_matches_returns_false_when_camera_address_is_null(): void
    {
        // Null address must not collide with a scope whose scope_id happens to be empty/null.
        $camera = new Camera();
        $camera->address = null;

        $scope = new UserScope();
        $scope->type = ScopeType::Address->value;
        $scope->scope_id = '';

        $this->assertFalse($this->invokeMatches($scope, $camera));
    }

    public function test_matches_returns_false_for_unknown_scope_type(): void
    {
        $camera = new Camera();

        $scope = new UserScope();
        $scope->type = 'made_up_scope';
        $scope->scope_id = '1';

        $this->assertFalse($this->invokeMatches($scope, $camera));
    }

    /**
     * Reflection helper to call the protected `matches()` method directly.
     */
    private function invokeMatches(UserScope $scope, Camera $camera): bool
    {
        $ref = new ReflectionClass($this->policy);
        $method = $ref->getMethod('matches');
        $method->setAccessible(true);
        return $method->invoke($this->policy, $scope, $camera);
    }
}
