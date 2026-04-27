<?php

namespace Auth\Repositories;

use Auth\Models\AuthLog;
use Auth\Repositories\Interfaces\AuthLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Eloquent implementation of the authorization log repository.
 */
class AuthLogRepository implements AuthLogRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function paginateByUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return AuthLog::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function latestActivity(): ?string
    {
        return AuthLog::query()
            ->latest('created_at')
            ->value('created_at');
    }

    /**
     * @inheritDoc
     */
    public function dailyLoginCountsByCompany(int $companyId, Carbon $from, int $days): array
    {
        $counts = AuthLog::query()
            ->where('company_id', $companyId)
            ->where('action', 'login')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $from->copy()->addDays($i)->format('Y-m-d');
            $series[] = ['date' => $day, 'count' => (int) ($counts[$day] ?? 0)];
        }
        return $series;
    }

    /**
     * @inheritDoc
     */
    public function countLoginsByCompanySince(int $companyId, Carbon $since): int
    {
        return (int) AuthLog::query()
            ->where('company_id', $companyId)
            ->where('action', 'login')
            ->where('created_at', '>=', $since)
            ->count();
    }

    /**
     * @inheritDoc
     */
    public function latestByCompany(int $companyId, int $limit): Collection
    {
        return AuthLog::query()
            ->where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function latestByUser(int $userId, int $limit): Collection
    {
        return AuthLog::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function latestLoginPerUser(array $userIds): Collection
    {
        if (empty($userIds)) {
            return new Collection();
        }

        return AuthLog::query()
            ->whereIn('user_id', $userIds)
            ->where('action', 'login')
            ->orderByDesc('created_at')
            ->get(['user_id', 'ip_address', 'user_agent', 'created_at'])
            ->unique('user_id')
            ->keyBy('user_id');
    }

    /**
     * @inheritDoc
     */
    public function latestActivityTimestampPerUser(array $userIds): Collection
    {
        if (empty($userIds)) {
            return new Collection();
        }

        $rows = AuthLog::query()
            ->select('user_id', DB::raw('MAX(created_at) as max_at'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->pluck('max_at', 'user_id');

        return new Collection($rows->all());
    }

    /**
     * @inheritDoc
     */
    public function log(string $action, string $model, int $userId, int $companyId, ?string $ip, ?string $userAgent): void
    {
        AuthLog::query()->create([
            'action' => $action,
            'model' => $model,
            'user_id' => $userId,
            'company_id' => $companyId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
