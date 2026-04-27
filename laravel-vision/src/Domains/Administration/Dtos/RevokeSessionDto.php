<?php

namespace Administration\Dtos;

/**
 * Input DTO for revoking a single Passport session belonging to a user in the caller's tenant.
 */
readonly class RevokeSessionDto
{
    /**
     * @param int $companyId tenant boundary — service refuses to revoke tokens of users from other tenants
     * @param int $userId user whose session is being revoked
     * @param string $sessionId Passport token id
     */
    public function __construct(
        private int $companyId,
        private int $userId,
        private string $sessionId,
    ) {}

    /**
     * @return int tenant id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * @return int user id
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string token id
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}
