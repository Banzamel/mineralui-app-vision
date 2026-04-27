<?php

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use Administration\Dtos\RoleDto;

class RoleDtoTest extends TestCase
{
    public function test_getters_return_correct_values(): void
    {
        $permissions = ['users.view', 'users.create'];
        $dto = new RoleDto('Manager', $permissions);

        $this->assertSame('Manager', $dto->getName());
        $this->assertSame($permissions, $dto->getPermissions());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new RoleDto('Admin', ['users.view']);

        $this->assertSame([
            'name' => 'Admin',
            'permissions' => ['users.view'],
        ], $dto->toArray());
    }

    public function test_empty_permissions_array(): void
    {
        $dto = new RoleDto('Viewer', []);

        $this->assertSame([], $dto->getPermissions());
        $this->assertSame([
            'name' => 'Viewer',
            'permissions' => [],
        ], $dto->toArray());
    }
}
