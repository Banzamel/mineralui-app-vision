<?php

namespace Administration\Dtos;

/**
 * Input DTO for resetting a user's password — carries the HTTP context needed for the audit log entry.
 */
readonly class ResetUserPasswordDto
{
    /**
     * @param int $userId user whose password is being reset
     * @param string|null $ip caller IP for the audit log
     * @param string|null $userAgent caller user-agent for the audit log
     */
    public function __construct(
        private int $userId,
        private ?string $ip,
        private ?string $userAgent,
    ) {}

    /**
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string|null caller IP
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @return string|null caller user-agent
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
