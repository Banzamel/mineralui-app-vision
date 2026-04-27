<?php

namespace System\Services\Interfaces;

use System\Dtos\SystemStatusDto;
use System\Dtos\SystemStatusQueryDto;

/**
 * System status service contract — exposes photo storage usage and application version
 * in a typed DTO so the service never leaks global state (auth, Eloquent) to callers.
 */
interface SystemStatusServiceInterface
{
    /**
     * Returns the current system status for the tenant carried in the query DTO.
     *
     * @param SystemStatusQueryDto $dto tenant scope
     * @return SystemStatusDto status payload
     */
    public function current(SystemStatusQueryDto $dto): SystemStatusDto;
}
