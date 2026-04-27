<?php

namespace Administration\Services\Interfaces;

use Administration\Dtos\AuthLogsSummaryQueryDto;
use Administration\Dtos\CompanyActivityQueryDto;
use Administration\Dtos\MyActivityQueryDto;
use Administration\Dtos\UserAuthLogsQueryDto;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Activity history service — reads auth log entries and presents them for the admin and personal panels.
 * Session management lives in UserSessionServiceInterface.
 */
interface UserActivityServiceInterface
{
    /**
     * Returns a paginated list of auth log entries for a single user, newest first.
     *
     * @param UserAuthLogsQueryDto $dto user and pagination parameters
     * @return LengthAwarePaginator paginated list of log entries
     */
    public function getUserAuthLogs(UserAuthLogsQueryDto $dto): LengthAwarePaginator;

    /**
     * Company-wide login summary — daily login counts (last 30 days) and totals
     * (users, active, logins_last_7_days).
     *
     * @param AuthLogsSummaryQueryDto $dto tenant scope
     * @return array{daily: array<int, array{date:string, count:int}>, totals: array{users:int, active:int, logins_last_7_days:int}}
     */
    public function authLogsSummary(AuthLogsSummaryQueryDto $dto): array;

    /**
     * Recent activity entries across all users in the tenant (login/logout/password_reset/model mutations).
     *
     * @param CompanyActivityQueryDto $dto tenant scope and row limit
     * @return array<int, array<string, mixed>> activity entries in the shape expected by the admin panel
     */
    public function listCompanyActivity(CompanyActivityQueryDto $dto): array;

    /**
     * Activity history of the current user (for the "My activity" drawer) mapped to the frontend's
     * ActivityType (login/logout/password_changed/avatar_changed/...).
     *
     * @param MyActivityQueryDto $dto acting user and row limit
     * @return array<int, array<string, mixed>> activity entries
     */
    public function myActivity(MyActivityQueryDto $dto): array;
}
