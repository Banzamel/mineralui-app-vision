<?php

namespace Tests\Unit\Enums;

use FileManager\Enums\StoragesEnum;
use PHPUnit\Framework\TestCase;

class StoragesEnumTest extends TestCase
{
    public function test_values(): void
    {
        $this->assertSame('local', StoragesEnum::local->value);
        $this->assertSame('public', StoragesEnum::public->value);
        $this->assertSame('aws', StoragesEnum::aws->value);
    }

    public function test_can_be_created_from_value(): void
    {
        $this->assertSame(StoragesEnum::local, StoragesEnum::from('local'));
        $this->assertSame(StoragesEnum::public, StoragesEnum::from('public'));
        $this->assertSame(StoragesEnum::aws, StoragesEnum::from('aws'));
    }

    public function test_try_from_invalid_value_returns_null(): void
    {
        $this->assertNull(StoragesEnum::tryFrom('gcs'));
    }

    public function test_has_exactly_three_cases(): void
    {
        $this->assertCount(3, StoragesEnum::cases());
    }
}
