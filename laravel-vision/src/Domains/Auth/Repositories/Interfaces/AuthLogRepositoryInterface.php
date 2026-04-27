<?php

namespace Auth\Repositories\Interfaces;

use Auth\Models\AuthLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * Authorization log repository contract — every read against the auth_logs table goes through here.
 */
interface AuthLogRepositoryInterface
{
    /**
     * Returns a paginated list of auth log entries for a single user, newest first.
     *
     * @param int $userId user whose log is being read
     * @param int $perPage rows per page
     * @return LengthAwarePaginator<AuthLog> paginator over the user's log entries
     */
    public function paginateByUser(int $userId, int $perPage): LengthAwarePaginator;

    /**
     * Returns the date of the most recent login activity in the system.
     *
     * @return string|null date of the latest entry or null if none exists
     */
    public function latestActivity(): ?string;

    /**
     * Returns daily login counts for a company within the window [from, from+days) ordered by date.
     *
     * @param int $companyId tenant id
     * @param Carbon $from window start (inclusive)
     * @param int $days window length in days
     * @return array<int, array{date:string, count:int}> zero-filled series, oldest first
     */
    public function dailyLoginCountsByCompany(int $companyId, Carbon $from, int $days): array;

    /**
     * Counts login entries of a company since a given timestamp.
     *
     * @param int $companyId tenant id
     * @param Carbon $since lower bound (inclusive)
     * @return int number of login rows
     */
    public function countLoginsByCompanySince(int $companyId, Carbon $since): int;

    /**
     * Returns the newest auth log entries for a whole company, capped at the given limit.
     *
     * @param int $companyId tenant id
     * @param int $limit row cap
     * @return Collection<int, AuthLog> newest first
     */
    public function latestByCompany(int $companyId, int $limit): Collection;

    /**
     * Returns the newest auth log entries for a single user, capped at the given limit.
     *
     * @param int $userId user id
     * @param int $limit row cap
     * @return Collection<int, AuthLog> newest first
     */
    public function latestByUser(int $userId, int $limit): Collection;

    /**
     * Returns the latest login row per user for a given set of user ids.
     * Used to decorate session listings with ip/user-agent from the login moment.
     *
     * @param array<int, int> $userIds user id set
     * @return Collection<int, AuthLog> keyed by user_id
     */
    public function latestLoginPerUser(array $userIds): Collection;

    /**
     * Returns the timestamp of the latest activity per user for a given set of user ids.
     *
     * @param array<int, int> $userIds user id set
     * @return Collection<int, string> keyed by user_id, value = MAX(created_at)
     */
    public function latestActivityTimestampPerUser(array $userIds): Collection;

    /**
     * Appends a single log row — used by services that need to record auth events
     * without touching the AuthLog model directly.
     *
     * @param string $action log action string (e.g. 'password_reset')
     * @param string $model fully-qualified model class the log relates to
     * @param int $userId user id the log concerns
     * @param int $companyId tenant id
     * @param string|null $ip request IP (nullable when recorded outside the HTTP context)
     * @param string|null $userAgent user-agent header value
     * @return void
     */
    public function log(string $action, string $model, int $userId, int $companyId, ?string $ip, ?string $userAgent): void;
}
