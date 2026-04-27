<?php

namespace System\Dtos;

/**
 * Output DTO describing photo storage usage against disk capacity.
 */
readonly class DiskUsageDto
{
    /**
     * Builds the DTO with raw byte counters and the derived percentage.
     *
     * @param int $usedBytes bytes consumed by photos in this tenant
     * @param int $totalBytes total disk capacity
     * @param int $percent percentage used on disk (0–100)
     */
    public function __construct(
        private int $usedBytes,
        private int $totalBytes,
        private int $percent,
    ) {}

    /**
     * Returns bytes used by the tenant's photos.
     *
     * @return int used bytes
     */
    public function getUsedBytes(): int
    {
        return $this->usedBytes;
    }

    /**
     * Returns total disk capacity in bytes.
     *
     * @return int total bytes
     */
    public function getTotalBytes(): int
    {
        return $this->totalBytes;
    }

    /**
     * Returns the percentage of disk used (0–100).
     *
     * @return int percent
     */
    public function getPercent(): int
    {
        return $this->percent;
    }
}
