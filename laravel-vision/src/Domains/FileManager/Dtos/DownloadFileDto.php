<?php

namespace FileManager\Dtos;

/**
 * Input DTO for downloading a file manager item.
 */
readonly class DownloadFileDto
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
