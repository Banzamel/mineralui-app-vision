<?php

namespace Installer\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Installer\Dtos\DatabaseConfigDto;
use Installer\Services\Interfaces\DatabaseTesterServiceInterface;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Service for testing the database connection and running migrations during installation.
 */
class DatabaseTesterService implements DatabaseTesterServiceInterface
{
    /**
     * @inheritDoc
     *
     * Attempts a PDO connection with the given MySQL credentials using a 3-second timeout.
     *
     * @throws RuntimeException When the connection cannot be established.
     */
    public function test(DatabaseConfigDto $dto): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $dto->host,
            $dto->port,
            $dto->database,
        );

        try {
            new PDO($dsn, $dto->username, $dto->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), previous: $e);
        }
    }

    /**
     * @inheritDoc
     *
     * Updates the runtime config with new data from .env and runs migrations + role/permission seeder.
     */
    public function migrate(): void
    {
        // Swap runtime config to the freshly-saved credentials so Artisan picks them up
        // without needing a full process restart.
        Config::set('database.connections.mysql.host', env('DB_HOST'));
        Config::set('database.connections.mysql.port', (int) env('DB_PORT', 3306));
        Config::set('database.connections.mysql.database', env('DB_DATABASE'));
        Config::set('database.connections.mysql.username', env('DB_USERNAME'));
        Config::set('database.connections.mysql.password', env('DB_PASSWORD'));
        DB::purge('mysql');

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', [
            '--class' => \Database\Seeders\RoleAndPermissionsSeeder::class,
            '--force' => true,
        ]);
    }
}
