<?php

namespace FileManager\Dtos;

/**
 * Input DTO for renaming/moving a file manager item.
 */
readonly class UpdateItemDto
{
    /**
     * @param int $pathId item id
     * @param string|null $name new name or null to leave unchanged
     * @param int|null $parentId new parent id or null to leave unchanged
     */
    public function __construct(
        private int $pathId,
        private ?string $name,
        private ?int $parentId,
    ) {}

    /**
     * @return int item id
     */
    public function getPathId(): int
    {
        return $this->pathId;
    }

    /**
     * @return string|null new name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return int|null new parent id
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }
}
