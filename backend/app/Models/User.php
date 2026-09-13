<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Hypervel\Database\Eloquent\Builder;
use Hypervel\Database\Eloquent\Collection;
use Hypervel\Database\Eloquent\Factories\HasFactory;
use Hypervel\Database\Eloquent\Relations\HasMany;
use Hypervel\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 *
 * @property int                                     $id
 * @property string|null                             $logto_id
 * @property string                                  $name
 * @property string                                  $email
 * @property Carbon|null                             $email_verified_at
 * @property string                                  $password
 * @property string|null                             $remember_token
 * @property string|null                             $avatar
 * @property string|null                             $phone
 * @property string|null                             $address
 * @property Carbon|null                             $last_login_at
 * @property array<string, mixed>|null               $custom_data
 * @property Carbon|null                             $created_at
 * @property Carbon|null                             $updated_at
 *
 * @property-read Collection<int, UserSignInLog>     $signInLogs
 * @property-read int|null                           $sign_in_logs_count
 * @property-read Collection<int, UserRole>          $roles
 * @property-read int|null                           $roles_count
 * @property-read Collection<int, PasswordChangeLog> $passwordChangeLogs
 * @property-read int|null                           $password_change_logs_count
 * @property-read Collection<int, PasswordChangeLog> $initiatedPasswordChanges
 * @property-read int|null                           $initiated_password_changes_count
 *
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereId(mixed $value)
 * @method static Builder<static>|User whereLogtoId(mixed $value)
 * @method static Builder<static>|User whereName(mixed $value)
 * @method static Builder<static>|User whereEmail(mixed $value)
 * @method static Builder<static>|User whereEmailVerifiedAt(mixed $value)
 * @method static Builder<static>|User wherePassword(mixed $value)
 * @method static Builder<static>|User whereRememberToken(mixed $value)
 * @method static Builder<static>|User whereAvatar(mixed $value)
 * @method static Builder<static>|User wherePhone(mixed $value)
 * @method static Builder<static>|User whereAddress(mixed $value)
 * @method static Builder<static>|User whereLastLoginAt(mixed $value)
 * @method static Builder<static>|User whereCustomData(mixed $value)
 * @method static Builder<static>|User whereCreatedAt(mixed $value)
 * @method static Builder<static>|User whereUpdatedAt(mixed $value)
 */
class User extends Authenticatable
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected array $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'logto_id',
        'avatar',
        'phone',
        'address',
        'last_login_at',
        'custom_data',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected array $hidden = [
        'password',
        'remember_token',
        'roles',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected array $casts = [
        'id'                => 'integer',
        'email_verified_at' => 'datetime',
        'password'          => 'string',
        'last_login_at'     => 'datetime',
        'custom_data'       => 'array',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Temporary storage for cached role names.
     *
     * @var array<int, string>
     */
    protected array $rolesCache = [];

    /**
     * Get the sign-in logs for the user.
     *
     * @return HasMany<UserSignInLog, $this>
     */
    public function signInLogs(): HasMany
    {
        return $this->hasMany(UserSignInLog::class, 'user_id', 'id');
    }

    /**
     * Get all roles assigned to the user.
     *
     * @return HasMany<UserRole, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

    /**
     * Get password change logs for this user (where user is target).
     *
     * @return HasMany<PasswordChangeLog, $this>
     */
    public function passwordChangeLogs(): HasMany
    {
        return $this->hasMany(PasswordChangeLog::class, 'user_id', 'id');
    }

    /**
     * Get password changes initiated by this user (where user is actor/admin).
     *
     * @return HasMany<PasswordChangeLog, $this>
     */
    public function initiatedPasswordChanges(): HasMany
    {
        return $this->hasMany(PasswordChangeLog::class, 'changed_by_user_id', 'id');
    }

    /**
     * Get role names as an array.
     *
     * @return array<int, string>
     */
    public function getRoleNames(): array
    {
        return $this->roles()->pluck('role')->toArray();
    }

    /**
     * Check if user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('role', $role)->exists();
    }

    /**
     * Check if the user is a superadmin.
     *
     * @return bool
     */
    public function isSuperadmin(): bool
    {
        return $this->hasRole(UserRole::ROLE_SUPERADMIN);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::ROLE_ADMIN);
    }

    /**
     * Check if the user is a regular user.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->hasRole(UserRole::ROLE_USER);
    }

    /**
     * Check if the user can manage other users.
     *
     * @return bool
     */
    public function canManageUsers(): bool
    {
        return $this->hasRole(UserRole::ROLE_SUPERADMIN) || $this->hasRole(UserRole::ROLE_ADMIN);
    }

    /**
     * Override toArray to include roles as a calculated property without persisting to DB.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        if ($this->relationLoaded('roles')) {
            $array['roles'] = $this->getRoleNames();
        } elseif (! empty($this->rolesCache)) {
            $array['roles'] = $this->rolesCache;
        }

        return $array;
    }

    /**
     * Override setAttribute to prevent 'roles' from being saved as raw attribute.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value): static
    {
        if ($key === 'roles' && is_array($value)) {
            $this->rolesCache = $value;

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Override getAttribute to return roles array if it was cached.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($key === 'roles' && ! empty($this->rolesCache)) {
            return $this->rolesCache;
        }

        return parent::getAttribute($key);
    }
}
