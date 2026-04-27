<?php

namespace Administration\Services\Interfaces;

use Administration\Dtos\CompanySessionsQueryDto;
use Administration\Dtos\RevokeSessionDto;
use Shared\Exceptions\ApiJsonException;

/**
 * Passport-session management contract scoped to a tenant — lists active sessions and revokes them.
 */
interface UserSessionServiceInterface
{
    /**
     * Returns the list of active Passport sessions for the tenant carried in the DTO,
     * decorated with the latest ip/user-agent per user.
     *
     * @param CompanySessionsQueryDto $dto tenant scope + current token id
     * @return array<int, array<string, mixed>> shape expected by the admin sessions panel
     */
    public function listCompanySessions(CompanySessionsQueryDto $dto): array;

    /**
     * Revokes a single session belonging to a user in the tenant carried in the DTO.
     *
     * @param RevokeSessionDto $dto tenant/user/session triple
     * @return void
     * @throws ApiJsonException when the user does not belong to the tenant or the session does not exist
     */
    public function revokeSession(RevokeSessionDto $dto): void;
}
