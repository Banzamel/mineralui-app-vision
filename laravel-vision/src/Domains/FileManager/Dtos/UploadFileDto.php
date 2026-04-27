<?php

namespace FileManager\Dtos;

use Illuminate\Http\UploadedFile;

/**
 * Input DTO for uploading a new file to the caller's tenant.
 */
readonly class UploadFileDto
{
    /**
     * @param int $companyId tenant id (source of the on-disk root path)
     * @param UploadedFile $file uploaded file
     * @param int|null $parentId target directory id or null for the company root
     * @param string|null $storage disk name or null for the default
     */
    public function __construct(
        private int $companyId,
        private UploadedFile $file,
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
     * @return UploadedFile uploaded file
     */
    public function getFile(): UploadedFile
    {
        return $this->file;
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
