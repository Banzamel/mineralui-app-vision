<?php

namespace Tests\Unit\Dtos;

use Auth\Dtos\RefreshLoginDto;
use PHPUnit\Framework\TestCase;

class RefreshLoginDtoTest extends TestCase
{
    public function test_getter_returns_correct_value(): void
    {
        $dto = new RefreshLoginDto('abc-refresh-token');

        $this->assertSame('abc-refresh-token', $dto->getRefreshToken());
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $dto = new RefreshLoginDto('abc-refresh-token');

        $this->assertSame([
            'refresh_token' => 'abc-refresh-token',
        ], $dto->toArray());
    }
}
