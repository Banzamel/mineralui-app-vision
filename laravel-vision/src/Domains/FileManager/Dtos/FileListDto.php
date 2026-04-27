<?php

namespace FileManager\Dtos;

/**
 * Input DTO for listing the contents of a file manager directory in the caller's tenant.
 */
readonly class FileListDto
{
    /**
     * @param int $companyId tenant id
     * @param int|null $parentId parent directory id or null for the company root
     */
    public function __construct(
        private int $companyId,
        private ?int $parentId,
    ) {}

    /**
     * @return int tenant id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * @return int|null parent directory id
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }
}
