<?php

namespace System\Services;

use Albums\Repositories\Interfaces\PhotoRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use System\Dtos\DiskUsageDto;
use System\Dtos\SystemStatusDto;
use System\Dtos\SystemStatusQueryDto;
use System\Services\Interfaces\SystemStatusServiceInterface;

/**
 * System status service — composes per-tenant photo bytes (via PhotoRepositoryInterface)
 * with disk capacity reported by the filesystem, returning an immutable SystemStatusDto.
 */
class SystemStatusService implements SystemStatusServiceInterface
{
    /**
     * @param PhotoRepositoryInterface $photoRepository photos repository from the Albums domain
     */
    public function __construct(
        protected PhotoRepositoryInterface $photoRepository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function current(SystemStatusQueryDto $dto): SystemStatusDto
    {
        $usedBytes = $this->photoRepository->sumBytesForCompany($dto->getCompanyId());

        $diskRoot = Storage::disk('public')->path('');
        $totalBytes = (int) (@disk_total_space($diskRoot) ?: 0);
        $freeBytes = (int) (@disk_free_space($diskRoot) ?: 0);

        // Filesystem call may return false in restricted envs — fall back to used = total (100%).
        if ($totalBytes <= 0) {
            $totalBytes = max($usedBytes, 1);
            $freeBytes = 0;
        }

        $usedOnDisk = max($totalBytes - $freeBytes, $usedBytes);
        $percent = $totalBytes > 0 ? (int) round($usedOnDisk / $totalBytes * 100) : 0;

        return new SystemStatusDto(
            disk: new DiskUsageDto(
                usedBytes: $usedBytes,
                totalBytes: $totalBytes,
                percent: $percent,
            ),
            version: (string) config('vision.version', '1.0.0'),
        );
    }
}
