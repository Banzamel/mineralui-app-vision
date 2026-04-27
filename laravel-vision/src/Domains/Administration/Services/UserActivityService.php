<?php

namespace Administration\Services;

use Administration\Dtos\AuthLogsSummaryQueryDto;
use Administration\Dtos\CompanyActivityQueryDto;
use Administration\Dtos\MyActivityQueryDto;
use Administration\Dtos\UserAuthLogsQueryDto;
use Administration\Repositories\Interfaces\UserRepositoryInterface;
use Administration\Services\Interfaces\UserActivityServiceInterface;
use Auth\Models\AuthLog;
use Auth\Repositories\Interfaces\AuthLogRepositoryInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Activity history service — reads auth log entries and decorates them for the admin and personal panels.
 * All Eloquent reads flow through AuthLogRepositoryInterface / UserRepositoryInterface.
 */
class UserActivityService implements UserActivityServiceInterface
{
    private const int SUMMARY_DAYS = 30;
    private const int LAST_LOGINS_WINDOW_DAYS = 7;

    /**
     * @param AuthLogRepositoryInterface $authLogRepository auth log repository
     * @param UserRepositoryInterface $userRepository users repository
     */
    public function __construct(
        protected AuthLogRepositoryInterface $authLogRepository,
        protected UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getUserAuthLogs(UserAuthLogsQueryDto $dto): LengthAwarePaginator
    {
        return $this->authLogRepository->paginateByUser($dto->getUserId(), $dto->getPerPage());
    }

    /**
     * @inheritDoc
     */
    public function authLogsSummary(AuthLogsSummaryQueryDto $dto): array
    {
        $companyId = $dto->getCompanyId();
        $from = Carbon::today()->subDays(self::SUMMARY_DAYS - 1);

        $daily = $this->authLogRepository->dailyLoginCountsByCompany($companyId, $from, self::SUMMARY_DAYS);
        $totalUsers = $this->userRepository->countByCompany($companyId);
        $activeUsers = $this->userRepository->countActiveByCompany($companyId);
        $logins7 = $this->authLogRepository->countLoginsByCompanySince(
            $companyId,
            Carbon::today()->subDays(self::LAST_LOGINS_WINDOW_DAYS - 1),
        );

        return [
            'daily' => $daily,
            'totals' => [
                'users' => $totalUsers,
                'active' => $activeUsers,
                'logins_last_7_days' => $logins7,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function listCompanyActivity(CompanyActivityQueryDto $dto): array
    {
        $entries = $this->authLogRepository->latestByCompany($dto->getCompanyId(), $dto->getLimit());
        $names = $this->resolveModelNames($entries);

        return $entries->map(fn (AuthLog $log): array => [
            'id' => (string) $log->id,
            'user_id' => (int) $log->user_id,
            'type' => $this->normalizeActivityType((string) $log->action),
            'ip' => $log->ip_address,
            'description' => $this->describeActivity($log, $names),
            'at' => optional($log->created_at)->toIso8601String(),
        ])->all();
    }

    /**
     * @inheritDoc
     */
    public function myActivity(MyActivityQueryDto $dto): array
    {
        $entries = $this->authLogRepository->latestByUser($dto->getUserId(), $dto->getLimit());
        $names = $this->resolveModelNames($entries);

        return $entries->map(function (AuthLog $log) use ($names): array {
            $type = $this->myActivityType($log);
            return [
                'id' => (string) $log->id,
                'type' => $type,
                'ip' => $log->ip_address,
                'description' => $this->myActivityDescription($log, $names, $type),
                'at' => optional($log->created_at)->toIso8601String(),
            ];
        })->all();
    }

    /**
     * Maps an AuthLog row to the activity type shown in the "My activity" drawer.
     *
     * @param AuthLog $log source log row
     * @return string activity type consumed by the frontend
     */
    private function myActivityType(AuthLog $log): string
    {
        $action = (string) $log->action;
        $model = (string) $log->model;
        $changes = (array) ($log->changes ?? []);

        return match (true) {
            $action === 'login' => 'login',
            $action === 'logout' => 'logout',
            str_contains($action, 'password') => 'password_changed',
            str_contains($action, 'scope') => 'scopes_updated',
            $model === \Administration\Models\User::class && $action === 'updated' && array_key_exists('avatar_path', $changes) => 'avatar_changed',
            $model === \Administration\Models\User::class && $action === 'updated' => 'profile_updated',
            $model === \Albums\Models\Album::class && $action === 'created' => 'album_created',
            $model === \Albums\Models\Photo::class && $action === 'created' => 'photo_uploaded',
            default => 'profile_updated',
        };
    }

    /**
     * Human-readable description (in Polish, displayed verbatim in the UI).
     * Short labels for auth events, describeActivity() for model mutations.
     *
     * @param AuthLog $log source log row
     * @param array<string, array<int, string>> $names model => [row_id => name] lookup
     * @param string $type activity type already resolved for this row
     * @return string Human-readable English sentence for the UI
     */
    private function myActivityDescription(AuthLog $log, array $names, string $type): string
    {
        if (in_array($type, ['login', 'logout', 'password_changed', 'avatar_changed', 'profile_updated', 'scopes_updated'], true)) {
            return match ($type) {
                'login' => 'Signed in',
                'logout' => 'Signed out',
                'password_changed' => 'Password changed',
                'avatar_changed' => 'Avatar updated',
                'profile_updated' => 'Profile updated',
                'scopes_updated' => 'Access scope updated',
                default => $type,
            };
        }
        return $this->describeActivity($log, $names);
    }

    /**
     * Maps the raw AuthLog action to the type expected by the frontend.
     * Loggable actions (created/updated/deleted/softDeleted/restored) collapse into 'model_change'.
     *
     * @param string $action raw log action
     * @return string normalised activity type
     */
    private function normalizeActivityType(string $action): string
    {
        return match (true) {
            $action === 'login' => 'login',
            $action === 'logout' => 'logout',
            str_contains($action, 'password') => 'password_reset',
            str_contains($action, 'scope') => 'scopes_updated',
            str_contains($action, 'role') => 'role_changed',
            in_array($action, ['created', 'updated', 'deleted', 'softDeleted', 'restored'], true) => 'model_change',
            default => $action,
        };
    }

    /**
     * Renders a human-readable description for an activity entry.
     * For model mutations (Loggable) pulls the name from the preloaded map or from the changes payload.
     *
     * @param AuthLog $log source log row
     * @param array<string, array<int, string>> $names model => [row_id => name] lookup
     * @return string Human-readable English sentence for the UI
     */
    private function describeActivity(AuthLog $log, array $names): string
    {
        $action = (string) $log->action;
        $type = $this->normalizeActivityType($action);

        if ($type !== 'model_change') {
            return match ($type) {
                'login' => 'Signed in',
                'logout' => 'Signed out',
                'password_reset' => 'Password reset',
                'scopes_updated' => 'Access scope updated',
                'role_changed' => 'Role changed',
                default => $action,
            };
        }

        $modelLabel = $this->modelLabel((string) $log->model);
        $changes = (array) ($log->changes ?? []);
        $name = $names[(string) $log->model][(int) $log->row_id] ?? ($changes['name'] ?? null);
        $suffix = $name ? " '{$name}'" : ($log->row_id ? ' #' . (int) $log->row_id : '');

        return match ($action) {
            'created' => "Created {$modelLabel}{$suffix}",
            'updated' => "Updated {$modelLabel}{$suffix}" . $this->summarizeChanges($changes),
            'deleted', 'softDeleted' => "Deleted {$modelLabel}{$suffix}",
            'restored' => "Restored {$modelLabel}{$suffix}",
            default => "{$action} {$modelLabel}{$suffix}",
        };
    }

    /**
     * English model label, used in sentences like "Created {label} '{name}'".
     *
     * @param string $modelClass fully qualified Eloquent class name
     * @return string English noun
     */
    private function modelLabel(string $modelClass): string
    {
        return match ($modelClass) {
            \Objects\Models\VisionObject::class => 'object',
            \Objects\Models\Camera::class => 'camera',
            \Albums\Models\Album::class => 'album',
            \Administration\Models\User::class => 'user',
            default => 'record',
        };
    }

    /**
     * Returns a parenthesized list of changed field names ("(name, address)") for update entries.
     *
     * @param array<string, mixed> $changes diff payload from the log row
     * @return string parenthesized list, empty when no user-facing fields changed
     */
    private function summarizeChanges(array $changes): string
    {
        $skip = ['updated_at', 'created_at', 'deleted_at'];
        $keys = array_diff(array_keys($changes), $skip);
        if (empty($keys)) {
            return '';
        }
        return ' (' . implode(', ', array_slice($keys, 0, 4)) . (count($keys) > 4 ? ', …' : '') . ')';
    }

    /**
     * Bulk-loads the 'name' attribute of every model referenced by Loggable entries
     * (including soft-deleted rows) to avoid N+1 queries when building descriptions.
     *
     * @param Collection<int, AuthLog> $entries source log rows
     * @return array<string, array<int, string>> model => [row_id => name]
     */
    private function resolveModelNames(Collection $entries): array
    {
        $byClass = [];
        foreach ($entries as $log) {
            if (!$log->model || !$log->row_id) {
                continue;
            }
            if ($this->normalizeActivityType((string) $log->action) !== 'model_change') {
                continue;
            }
            $byClass[(string) $log->model][] = (int) $log->row_id;
        }

        $resolved = [];
        foreach ($byClass as $class => $ids) {
            if (!class_exists($class)) {
                continue;
            }
            $ids = array_values(array_unique($ids));
            $query = $class::query();
            if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
                $query->withTrashed();
            }

            // Pick a column that's actually present on the table — `name` first (most domain
            // models have it), then fall back to common alternatives. Album has no `name` column
            // so we use `folder_name`; Photo uses `filename`. Anything without a label column
            // gets skipped — UI will show the model id instead.
            $table = (new $class())->getTable();
            $labelColumn = match (true) {
                Schema::hasColumn($table, 'name') => 'name',
                Schema::hasColumn($table, 'display_name') => 'display_name',
                Schema::hasColumn($table, 'folder_name') => 'folder_name',
                Schema::hasColumn($table, 'filename') => 'filename',
                Schema::hasColumn($table, 'title') => 'title',
                default => null,
            };

            if ($labelColumn === null) {
                continue;
            }

            $rows = $query->whereIn('id', $ids)->pluck($labelColumn, 'id');
            $resolved[$class] = $rows->all();
        }
        return $resolved;
    }
}
