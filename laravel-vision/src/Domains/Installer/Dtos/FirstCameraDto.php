<?php

namespace Installer\Dtos;

/**
 * DTO with the data of the first camera created during installation.
 */
final readonly class FirstCameraDto
{
    /**
     * @param string $name Technical camera name.
     * @param string|null $displayName Name displayed in the interface.
     * @param string|null $address Free-text location label (e.g. "1st floor, staircase A").
     * @param string|null $ip Camera IP address.
     * @param string|null $streamUrl Stream URL (RTSP/HTTP).
     * @param string|null $streamLogin Stream login.
     * @param string|null $streamPassword Stream password (plain text, the service will encrypt it).
     */
    public function __construct(
        public string $name,
        public ?string $displayName = null,
        public ?string $address = null,
        public ?string $ip = null,
        public ?string $streamUrl = null,
        public ?string $streamLogin = null,
        public ?string $streamPassword = null,
    ) {}

    /**
     * Returns the data as an associative array with snake_case keys.
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'display_name' => $this->displayName,
            'address' => $this->address,
            'ip' => $this->ip,
            'stream_url' => $this->streamUrl,
            'stream_login' => $this->streamLogin,
            'stream_password' => $this->streamPassword,
        ];
    }
}
