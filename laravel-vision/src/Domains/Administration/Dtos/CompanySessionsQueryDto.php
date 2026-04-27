<?php

namespace Administration\Dtos;

/**
 * Input DTO for listing active Passport sessions across a whole tenant.
 * Carries the current token id so the service can flag the caller's own session.
 */
readonly class CompanySessionsQueryDto
{
    /**
     * @param int $companyId tenant id
     * @param string|null $currentTokenId token id of the calling session, or null when unavailable
     */
    public function __construct(
        private int $companyId,
        private ?string $currentTokenId,
    ) {}

    /**
     * @return int tenant id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * @return string|null current session token id
     */
    public function getCurrentTokenId(): ?string
    {
        return $this->currentTokenId;
    }
}
