<?php

namespace Installer\Services\Interfaces;

use Installer\Dtos\AdminDto;
use Installer\Dtos\DatabaseConfigDto;
use Installer\Dtos\FirstCameraDto;
use Installer\Dtos\FirstObjectDto;
use Installer\Enums\InstallStage;

/**
 * Contract for the main installation wizard service.
 */
interface InstallerServiceInterface
{
    /**
     * Returns the current installer state along with its stage and full database defaults
     * loaded from .env (including password — endpoint is gated by install.gate middleware,
     * so it is only reachable before the installation has been finalised).
     *
     * @return array{installed: bool, stage: string, database_defaults: array{host:string, port:int, database:string, username:string, password:string}}
     */
    public function status(): array;

    /**
     * Checks whether the application is already installed.
     *
     * @return bool True if installation has been completed.
     */
    public function isInstalled(): bool;

    /**
     * Returns the current wizard stage.
     *
     * @return InstallStage Current install stage.
     */
    public function getStage(): InstallStage;

    /**
     * Tries to connect to the database with the data from the DTO (without persisting it).
     *
     * @param DatabaseConfigDto $dto Database access data.
     */
    public function testDatabase(DatabaseConfigDto $dto): void;

    /**
     * Persists database access data into .env and runs migrations.
     *
     * @param DatabaseConfigDto $dto Database access data.
     */
    public function saveDatabase(DatabaseConfigDto $dto): void;

    /**
     * Stores administrator data in the installer payload.
     *
     * @param AdminDto $dto Administrator data (with plain text password).
     */
    public function saveAdmin(AdminDto $dto): void;

    /**
     * Creates the first company along with the administrator and the root tree object.
     *
     * @param FirstObjectDto $dto First object data.
     * @return array{company_id: int, user_id: int, object_id: int} IDs of the created entities.
     */
    public function saveFirstObject(FirstObjectDto $dto): array;

    /**
     * Persists the first camera against the freshly created root object.
     *
     * @param FirstCameraDto $dto First camera data (RTSP password in plaintext — service encrypts).
     */
    public function saveFirstCamera(FirstCameraDto $dto): void;

    /**
     * Finalizes the installation (sets the APP_INSTALLED flag and dispatches the event).
     */
    public function finalize(): void;
}
