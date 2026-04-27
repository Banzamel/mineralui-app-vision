<?php

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use Administration\Dtos\UserUpdateDto;

class UserUpdateDtoTest extends TestCase
{
    public function test_all_fields_nullable_by_default(): void
    {
        $dto = new UserUpdateDto();

        $this->assertNull($dto->getName());
        $this->assertNull($dto->getEmail());
        $this->assertNull($dto->getPassword());
        $this->assertNull($dto->getRoleName());
        $this->assertNull($dto->isActive());
    }

    public function test_to_array_filters_null_values(): void
    {
        $dto = new UserUpdateDto(name: 'Updated Name');

        $array = $dto->toArray();

        $this->assertSame(['name' => 'Updated Name'], $array);
        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('is_active', $array);
    }

    public function test_to_array_with_all_fields(): void
    {
        $dto = new UserUpdateDto('John', 'john@test.com', 'newpass', 'Admin', false);

        $this->assertSame([
            'name' => 'John',
            'email' => 'john@test.com',
            'password' => 'newpass',
            'is_active' => false,
        ], $dto->toArray());
    }

    public function test_to_array_excludes_role_name(): void
    {
        $dto = new UserUpdateDto(roleName: 'Operator');

        $this->assertEmpty($dto->toArray());
        $this->assertSame('Operator', $dto->getRoleName());
    }

    public function test_empty_dto_returns_empty_array(): void
    {
        $dto = new UserUpdateDto();

        $this->assertSame([], $dto->toArray());
    }
}
