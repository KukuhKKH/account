<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int    $id
 * @property int    $user_id
 * @property string $role
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static Builder whereRole(string $role)
 * @method static Builder whereUserId(int $userId)
 */
class UserRole extends Model
{
    public const ROLE_SUPERADMIN = 'Superadmin';
    public const ROLE_ADMIN      = 'Admin Account';
    public const ROLE_USER       = 'User';

    protected $table = 'user_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'role',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'user_id'    => 'integer',
        'role'       => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all available roles.
     *
     * @return array<string>
     */
    public static function getAllRoles(): array
    {
        return [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_USER,
        ];
    }

    /**
     * Get the user that owns this role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
