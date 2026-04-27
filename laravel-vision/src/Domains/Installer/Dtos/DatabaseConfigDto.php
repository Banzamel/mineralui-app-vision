<?php

namespace Installer\Dtos;

/**
 * DTO with database connection parameters for the installer.
 */
final readonly class DatabaseConfigDto
{
    /**
     * @param string $host Database host address.
     * @param int $port Database port (e.g. 3306).
     * @param string $database Database name.
     * @param string $username Database login.
     * @param string $password Database password.
     */
    public function __construct(
        public string $host,
        public int $port,
        public string $database,
        public string $username,
        public string $password,
    ) {}

    /**
     * Returns the data as an associative array (lowercase keys).
     *
     * @return array<string, string|int> Connection data.
     */
    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    /**
     * Returns the data as an array ready to be written to the .env file.
     *
     * @return array<string, string> Map of DB_* keys for EnvWriter.
     */
    public function toEnv(): array
    {
        return [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $this->host,
            'DB_PORT' => (string) $this->port,
            'DB_DATABASE' => $this->database,
            'DB_USERNAME' => $this->username,
            'DB_PASSWORD' => $this->password,
        ];
    }
}
