<?php

namespace Installer\Repositories\Interfaces;

use Installer\Enums\InstallStage;

/**
 * Contract of the installer state repository - holds the current stage and wizard step payloads.
 */
interface InstallStateRepositoryInterface
{
    /**
     * Checks whether the application has already been installed.
     *
     * @return bool True if the installation has been completed.
     */
    public function isInstalled(): bool;

    /**
     * Returns the current wizard stage.
     *
     * @return InstallStage Current stage.
     */
    public function getStage(): InstallStage;

    /**
     * Returns the payload stored under the given key.
     *
     * @param string $key Payload key.
     * @return array<string, mixed>|null Payload data or null when missing.
     */
    public function getPayload(string $key): ?array;

    /**
     * Stores a payload under the given key.
     *
     * @param string $key Payload key.
     * @param array<string, mixed> $value Data to be stored.
     */
    public function putPayload(string $key, array $value): void;

    /**
     * Advances the state to the given stage (only if it is later than the current one).
     *
     * @param InstallStage $stage Stage to be set.
     */
    public function markStage(InstallStage $stage): void;

    /**
     * Marks the installation as completed and clears the payloads.
     */
    public function finalize(): void;

    /**
     * Clears the entire installer state (deletes the file).
     */
    public function reset(): void;

    /**
     * Returns the entire state with a fallback to the default structure.
     *
     * @return array<string, mixed> Full state.
     */
    public function all(): array;
}
