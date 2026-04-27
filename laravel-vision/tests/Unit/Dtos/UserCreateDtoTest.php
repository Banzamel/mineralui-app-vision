<?php

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use Administration\Dtos\UserCreateDto;

class UserCreateDtoTest extends TestCase
{
    public function test_getters_return_correct_values(): void
    {
        $dto = new UserCreateDto('John', 'john@example.com', '', 'Operator', false);

        $this->assertSame('John', $dto->getName());
        $this->assertSame('john@example.com', $dto->getEmail());
        $this->assertSame('', $dto->getPassword());
        $this->assertSame('Operator', $dto->getRoleName());
        $this->assertFalse($dto->isActive());
    }

    public function test_is_active_defaults_to_true(): void
    {
        $dto = new UserCreateDto('John', 'john@example.com', '', 'Operator');

        $this->assertTrue($dto->isActive());
    }

    public function test_to_array_maps_camel_to_snake_case(): void
    {
        $dto = new UserCreateDto('John', 'john@example.com', '', 'Operator', true);

        $this->assertSame([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => '',
            'role' => 'Operator',
            'is_active' => true,
        ], $dto->toArray());
    }

    public function test_to_array_maps_role_name_to_role_key(): void
    {
        $dto = new UserCreateDto('John', 'john@example.com', '', 'Administrator');

        $array = $dto->toArray();

        $this->assertArrayHasKey('role', $array);
        $this->assertArrayNotHasKey('role_name', $array);
        $this->assertSame('Administrator', $array['role']);
    }
}
