<?php

namespace Tests\Unit\Dtos;

use Auth\Dtos\LoginDto;
use PHPUnit\Framework\TestCase;

class LoginDtoTest extends TestCase
{
    public function test_getters_return_correct_values(): void
    {
        $dto = new LoginDto('test@example.com', 'secret123');

        $this->assertSame('test@example.com', $dto->getEmail());
        $this->assertSame('secret123', $dto->getPassword());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new LoginDto('test@example.com', 'secret123');

        $this->assertSame([
            'email' => 'test@example.com',
            'password' => 'secret123',
        ], $dto->toArray());
    }
}
