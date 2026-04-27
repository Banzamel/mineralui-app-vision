<?php

namespace Tests\Unit\Enums;

use FileManager\Enums\EntityTypeEnum;
use PHPUnit\Framework\TestCase;

class EntityTypeEnumTest extends TestCase
{
    public function test_file_has_correct_value(): void
    {
        $this->assertSame('file', EntityTypeEnum::file->value);
    }

    public function test_dir_has_correct_value(): void
    {
        $this->assertSame('dir', EntityTypeEnum::dir->value);
    }

    public function test_can_be_created_from_value(): void
    {
        $this->assertSame(EntityTypeEnum::file, EntityTypeEnum::from('file'));
        $this->assertSame(EntityTypeEnum::dir, EntityTypeEnum::from('dir'));
    }

    public function test_try_from_invalid_value_returns_null(): void
    {
        $this->assertNull(EntityTypeEnum::tryFrom('symlink'));
    }

    public function test_has_exactly_two_cases(): void
    {
        $this->assertCount(2, EntityTypeEnum::cases());
    }
}
