<?php

namespace Installer\Services\Interfaces;

/**
 * Contract for the service that writes changes to the .env file.
 */
interface EnvWriterServiceInterface
{
    /**
     * Updates values inside the .env file.
     *
     * @param array<string, string|int|bool> $values Key=>value map to be set.
     */
    public function update(array $values): void;
}
