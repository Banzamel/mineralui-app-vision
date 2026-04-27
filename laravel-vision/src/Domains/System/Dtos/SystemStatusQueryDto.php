<?php

namespace System\Dtos;

/**
 * Input DTO for the system-status query — carries the tenant scope from the request.
 */
readonly class SystemStatusQueryDto
{
    /**
     * Builds the DTO with the company whose photo storage usage is being reported.
     *
     * @param int $companyId tenant id
     */
    public function __construct(
        private int $companyId,
    ) {}

    /**
     * Returns the tenant id.
     *
     * @return int company id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }
}
