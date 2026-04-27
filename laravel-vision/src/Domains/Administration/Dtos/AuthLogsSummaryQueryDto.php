<?php

namespace Administration\Dtos;

/**
 * Input DTO for the auth-logs summary (daily login counts + user totals) scoped to a tenant.
 */
readonly class AuthLogsSummaryQueryDto
{
    /**
     * @param int $companyId tenant id
     */
    public function __construct(
        private int $companyId,
    ) {}

    /**
     * @return int tenant id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }
}
