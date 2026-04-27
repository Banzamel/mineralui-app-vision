<?php

namespace FileManager\Dtos;

/**
 * Input DTO for deleting a file manager item.
 */
readonly class DeleteItemDto
{
    /**
     * @param int $pathId item id
     */
    public function __construct(
        private int $pathId,
    ) {}

    /**
     * @return int item id
     */
    public function getPathId(): int
    {
        return $this->pathId;
    }
}
