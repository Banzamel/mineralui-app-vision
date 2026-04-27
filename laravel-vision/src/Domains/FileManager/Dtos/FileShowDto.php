<?php

namespace FileManager\Dtos;

/**
 * Input DTO for fetching a single file manager item by id.
 */
readonly class FileShowDto
{
    /**
     * @param int $pathId file manager path id
     */
    public function __construct(
        private int $pathId,
    ) {}

    /**
     * @return int file manager path id
     */
    public function getPathId(): int
    {
        return $this->pathId;
    }
}
