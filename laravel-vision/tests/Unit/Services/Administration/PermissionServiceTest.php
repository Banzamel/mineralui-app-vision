<?php

namespace Tests\Unit\Services\Administration;

use Administration\Services\PermissionService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PermissionServiceTest extends TestCase
{
    public function test_returns_empty_array_when_no_modules_configured(): void
    {
        Config::set('permission.modules', []);

        $service = new PermissionService();

        $this->assertSame([], $service->getAllPermissions());
    }

}
