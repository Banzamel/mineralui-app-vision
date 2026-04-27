<?php

namespace Administration\Dtos;

/**
 * Input DTO for listing recent activity (auth log entries) across a whole tenant.
 */
readonly class CompanyActivityQueryDto
{
    /**
     * @param int $companyId tenant id
     * @param int $limit maximum number of entries to return
     */
    public function __construct(
        private int $companyId,
        private int $limit,
    ) {}

    /**
     * @return int tenant id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * @return int row limit
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
