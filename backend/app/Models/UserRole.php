<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Hypervel\Database\Eloquent\Builder;
use Hypervel\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserRole
 *
 * @property int         $id
 * @property int         $user_id
 * @property string      $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User   $user
 *
 * @method static Builder<static>|UserRole newModelQuery()
 * @method static Builder<static>|UserRole newQuery()
 * @method static Builder<static>|UserRole query()
 * @method static Builder<static>|UserRole whereId(mixed $value)
 * @method static Builder<static>|UserRole whereUserId(mixed $value)
 * @method static Builder<static>|UserRole whereRole(mixed $value)
 * @method static Builder<static>|UserRole whereCreatedAt(mixed $value)
 * @method static Builder<static>|UserRole whereUpdatedAt(mixed $value)
 */
class UserRole extends Model
{
    public const string ROLE_SUPERADMIN = 'Superadmin';
    public const string ROLE_ADMIN      = 'Admin Account';
    public const string ROLE_USER       = 'User';

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected array $fillable = [
        'user_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected array $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected array $casts = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'role'       => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all available valid roles.
     *
     * @return array<int, string>
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
     * Get the user that owns this role assignment.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
