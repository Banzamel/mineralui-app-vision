<?php

namespace Installer\Services\Interfaces;

use Installer\Dtos\DatabaseConfigDto;

/**
 * Contract for the service that tests the database connection and runs migrations.
 */
interface DatabaseTesterServiceInterface
{
    /**
     * Tests the database connection using the data from the DTO.
     *
     * @param DatabaseConfigDto $dto Access data.
     */
    public function test(DatabaseConfigDto $dto): void;

    /**
     * Runs migrations and the base seeder against the freshly configured database.
     */
    public function migrate(): void;
}
