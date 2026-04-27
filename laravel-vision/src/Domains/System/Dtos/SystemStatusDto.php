<?php

namespace System\Dtos;

/**
 * Output DTO aggregating the full system status payload.
 */
readonly class SystemStatusDto
{
    /**
     * Builds the DTO with the disk usage breakdown and the application version.
     *
     * @param DiskUsageDto $disk disk usage report
     * @param string $version application version string
     */
    public function __construct(
        private DiskUsageDto $disk,
        private string $version,
    ) {}

    /**
     * Returns the disk usage breakdown.
     *
     * @return DiskUsageDto disk usage DTO
     */
    public function getDisk(): DiskUsageDto
    {
        return $this->disk;
    }

    /**
     * Returns the application version string.
     *
     * @return string version
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
