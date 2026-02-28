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
 * @property int                                 $id
 * @property string                              $name
 * @property string                              $email
 * @property string|null                         $email_verified_at
 * @property string                              $password
 * @property string|null                         $remember_token
 * @property string|null                         $logto_id
 * @property string|null                         $avatar
 * @property string|null                         $phone
 * @property string|null                         $address
 * @property string                              $role
 * @property Carbon|null                         $last_login_at
 * @property array|null                          $custom_data
 * @property Carbon                              $created_at
 * @property Carbon                              $updated_at
 *
 * @property-read Collection<int, UserSignInLog> $signInLogs
 * @property-read int|null                       $sign_in_logs_count
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
 * @method static Builder<static> whereRole($value)
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
    use HasFactory, Notifiable;

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
        'role',
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
     */
    public function signInLogs(): HasMany
    {
        return $this->hasMany(UserSignInLog::class);
    }

    /**
     * Check if the user is a superadmin.
     */
    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if the user can manage users.
     */
    public function canManageUsers(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }
}
