<?php

namespace FileManager\Dtos;

/**
 * Input DTO for creating a new directory in the caller's tenant.
 */
readonly class CreateDirectoryDto
{
    /**
     * @param int $companyId tenant id (source of the on-disk root path)
     * @param string $name directory name (will be slugified on disk)
     * @param int|null $parentId parent directory id or null for the company root
     * @param string|null $storage disk name (local, public, aws…) or null for the default
     */
    public function __construct(
        private int $companyId,
        private string $name,
        private ?int $parentId,
        private ?string $storage,
    ) {}

    /**
     * @return int tenant id
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * @return string directory name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int|null parent directory id
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * @return string|null disk name or null for default
     */
    public function getStorage(): ?string
    {
        return $this->storage;
    }
}
