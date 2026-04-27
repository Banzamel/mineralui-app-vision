<?php

namespace Albums\Dtos;

/**
 * Input DTO for listing photos in an album with cursor pagination.
 */
readonly class PhotoListDto
{
    /**
     * @param int $albumId album whose photos are being listed
     * @param int $perPage page size (row limit per cursor page)
     * @param string|null $cursor opaque cursor token from the previous page, or null for the first page
     */
    public function __construct(
        private int $albumId,
        private int $perPage,
        private ?string $cursor,
    ) {}

    /**
     * @return int album id
     */
    public function getAlbumId(): int
    {
        return $this->albumId;
    }

    /**
     * @return int page size
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return string|null cursor token
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }
}
