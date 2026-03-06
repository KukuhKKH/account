<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int                                     $id
 * @property string                                  $name
 * @property string                                  $email
 * @property string|null                             $email_verified_at
 * @property string                                  $password
 * @property string|null                             $remember_token
 * @property string|null                             $logto_id
 * @property string|null                             $avatar
 * @property string|null                             $phone
 * @property string|null                             $address
 * @property Carbon|null                             $last_login_at
 * @property array|null                              $custom_data
 * @property Carbon                                  $created_at
 * @property Carbon                                  $updated_at
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
 * @method static Builder<static> newModelQuery()
 * @method static Builder<static> newQuery()
 * @method static Builder<static> query()
 * @method static Builder<static> whereId($value)
 * @method static Builder<static> whereName($value)
 * @method static Builder<static> whereEmail($value)
 * @method static Builder<static> whereLogtoId($value)
 * @method static Builder<static> whereAvatar($value)
 * @method static Builder<static> wherePhone($value)
 * @method static Builder<static> whereAddress($value)
 * @method static Builder<static> whereLastLoginAt($value)
 * @method static Builder<static> whereCustomData($value)
 * @method static Builder<static> whereCreatedAt($value)
 * @method static Builder<static> whereUpdatedAt($value)
 *
 * @method static UserFactory factory()
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * Temporary storage for roles array
     */
    protected array $rolesCache = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'roles',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_login_at'     => 'datetime',
            'custom_data'       => 'array',
        ];
    }

    /**
     * Get the sign-in logs for the user.
     *
     * @return HasMany
     */
    public function signInLogs(): HasMany
    {
        return $this->hasMany(UserSignInLog::class);
    }

    /**
     * Get all roles assigned to the user from Logto.
     *
     * @return HasMany
     */
    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Get password change logs for this user.
     * Returns all password changes made to this user's account.
     *
     * @return HasMany
     */
    public function passwordChangeLogs(): HasMany
    {
        return $this->hasMany(PasswordChangeLog::class, 'user_id');
    }

    /**
     * Get password changes initiated by this user.
     * Returns all password changes this user performed as an admin.
     *
     * @return HasMany
     */
    public function initiatedPasswordChanges(): HasMany
    {
        return $this->hasMany(PasswordChangeLog::class, 'changed_by_user_id');
    }

    /**
     * Get role names as array.
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
     * Check if the user can manage users.
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
        } elseif (!empty($this->rolesCache)) {
            $array['roles'] = $this->rolesCache;
        }

        return $array;
    }

    /**
     * Override setAttribute to prevent 'roles' from being persisted to database.
     *
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value): static
    {
        if ($key === 'roles') {
            $this->rolesCache = $value;
            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Override getAttribute to return roles array if it was set.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($key === 'roles' && !empty($this->rolesCache)) {
            return $this->rolesCache;
        }

        return parent::getAttribute($key);
    }

    /**
     * Boot the model with event listeners.
     * Prevents roles attribute from being saved to database.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            unset($model->attributes['roles']);
            unset($model->attributes['_roles_array']);
            unset($model->attributes['_rolesArray']);
        });
    }
}
