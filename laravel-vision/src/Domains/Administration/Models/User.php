<?php

namespace Administration\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Administration\Observers\UserObserver;
use Shared\Scopes\HasActiveScope;
use Shared\Traits\BelongsToCompany;
use Shared\Traits\Loggable;
use Spatie\Permission\Traits\HasRoles;

/**
 * System user model representing an account in the sec_users table.
 * Handles authentication, roles, permissions, company association and avatar.
 */
#[ObservedBy(UserObserver::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Loggable, Notifiable, SoftDeletes, HasRoles, BelongsToCompany, HasActiveScope;

    protected $table = 'sec_users';

    /**
     * Guard name for the Spatie permissions package.
     *
     * @var string
     */
    protected $guard_name = 'api';

    /**
     * Fields available for mass assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'avatar_path',
    ];

    /**
     * Fields hidden when serializing the model to JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Fields appended to the model on serialization (computed dynamically).
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
        'last_login_at',
        'is_online',
        'scopes',
    ];

    /**
     * Inactivity threshold (minutes) — last oauth_logs entry within this window means the user is online.
     *
     * @var int
     */
    public const ONLINE_THRESHOLD_MINUTES = 15;

    /**
     * Per-process cache mapping user_id => last login datetime / last activity datetime.
     * Primed via ::primeActivityCache() when listing users to avoid N+1 queries during serialization.
     *
     * @var array<int, \Illuminate\Support\Carbon|null>
     */
    protected static array $lastLoginCache = [];

    /**
     * @var array<int, \Illuminate\Support\Carbon|null>
     */
    protected static array $lastActivityCache = [];

    /**
     * Bulk-loads last login and last activity timestamps for the given user ids
     * so per-row attribute accessors don't fire N+1 queries during list serialization.
     *
     * @param array<int, int> $userIds
     * @return void
     */
    public static function primeActivityCache(array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $logins = \Auth\Models\AuthLog::query()
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('MAX(created_at) as max_at'))
            ->whereIn('user_id', $userIds)
            ->where('action', 'login')
            ->groupBy('user_id')
            ->pluck('max_at', 'user_id');

        $any = \Auth\Models\AuthLog::query()
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('MAX(created_at) as max_at'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->pluck('max_at', 'user_id');

        foreach ($userIds as $id) {
            self::$lastLoginCache[$id] = isset($logins[$id]) ? \Illuminate\Support\Carbon::parse($logins[$id]) : null;
            self::$lastActivityCache[$id] = isset($any[$id]) ? \Illuminate\Support\Carbon::parse($any[$id]) : null;
        }
    }

    /**
     * Returns the field-to-type cast map (e.g. dates, passwords).
     *
     * @return array<string, string> field => cast type
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed'
        ];
    }

    /**
     * Returns the public URL of the user's avatar image or null when none is set.
     *
     * @return string|null full avatar URL or null when not set
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar_path) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }

    /**
     * Last login timestamp (from oauth_logs, action=login). Uses the static cache when primed
     * via primeActivityCache(); otherwise issues a single MAX() query on demand.
     *
     * @return string|null ISO8601 string or null when the user has never logged in.
     */
    public function getLastLoginAtAttribute(): ?string
    {
        $userId = (int) $this->getAttribute('id');
        if (!array_key_exists($userId, self::$lastLoginCache)) {
            $value = \Auth\Models\AuthLog::query()
                ->where('user_id', $userId)
                ->where('action', 'login')
                ->max('created_at');
            self::$lastLoginCache[$userId] = $value ? \Illuminate\Support\Carbon::parse($value) : null;
        }
        return optional(self::$lastLoginCache[$userId])->toIso8601String();
    }

    /**
     * True when the user has any oauth_logs entry within the last ONLINE_THRESHOLD_MINUTES minutes
     * (login/logout/created/updated/...). Uses the static cache when primed.
     *
     * @return bool
     */
    public function getIsOnlineAttribute(): bool
    {
        $userId = (int) $this->getAttribute('id');
        if (!array_key_exists($userId, self::$lastActivityCache)) {
            $value = \Auth\Models\AuthLog::query()->where('user_id', $userId)->max('created_at');
            self::$lastActivityCache[$userId] = $value ? \Illuminate\Support\Carbon::parse($value) : null;
        }
        $last = self::$lastActivityCache[$userId];
        return $last !== null && $last->greaterThan(now()->subMinutes(self::ONLINE_THRESHOLD_MINUTES));
    }

    /**
     * Returns the relation to external social accounts (Google, Facebook, etc.) linked to the user.
     *
     * @return HasMany one-to-many relation with external accounts
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(\Auth\Models\SocialAccount::class);
    }

    /**
     * Visibility scopes the user has been granted (rows in `vision_user_scopes`).
     * Named `userScopes` so the `getScopesAttribute()` JSON accessor (which serves the frontend
     * shape `[{type, id}, ...]`) doesn't clash with the relation name.
     *
     * @return HasMany
     */
    public function userScopes(): HasMany
    {
        return $this->hasMany(\Objects\Models\UserScope::class, 'user_id');
    }

    /**
     * Frontend-friendly view of the visibility scopes — `[{type, id}, ...]` derived from
     * the `userScopes` relation. Eager-load `userScopes` in list queries to avoid N+1
     * during serialization (paginateByCompany already does it).
     *
     * @return array<int, array{type: string, id: int}>
     */
    public function getScopesAttribute(): array
    {
        return $this->userScopes
            ->map(fn ($scope) => ['type' => $scope->type, 'id' => (int) $scope->scope_id])
            ->all();
    }

    /**
     * Returns the class name used by Laravel's morph mechanism.
     * Must match the enforceMorphMap entry so that Spatie permissions
     * work jointly for Administration\User and Auth\User (which extends this model).
     *
     * @return string fixed morph name - "user"
     */
    public function getMorphClass(): string
    {
        return 'user';
    }
}
