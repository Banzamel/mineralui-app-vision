<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        // Override Docker env vars before the app boots
            //  docker exec mysql mysql -uroot -proot_password -e "CREATE DATABASE IF NOT EXISTS vision_test;"
            //  docker exec mysql mysql -uroot -proot_password -e "GRANT ALL PRIVILEGES ON vision_test.* TO 'vision'@'%';"
        $overrides = [
            'APP_URL' => 'http://localhost',
            'DB_DATABASE' => 'vision_test',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
            'BCRYPT_ROUNDS' => '4',
            'BROADCAST_CONNECTION' => 'log',
        ];

        foreach ($overrides as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        return parent::createApplication();
    }
}
