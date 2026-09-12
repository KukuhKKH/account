<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Hypervel\Database\Eloquent\Builder;
use Hypervel\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserSignInLog
 *
 * @property int                       $id
 * @property int                       $user_id
 * @property string|null               $ip_address
 * @property string|null               $user_agent
 * @property array<string, mixed>|null $device_info
 * @property Carbon                    $signed_in_at
 * @property string|null               $logto_event_id
 *
 * @property-read User                 $user
 *
 * @method static Builder<static>|UserSignInLog newModelQuery()
 * @method static Builder<static>|UserSignInLog newQuery()
 * @method static Builder<static>|UserSignInLog query()
 * @method static Builder<static>|UserSignInLog whereId(mixed $value)
 * @method static Builder<static>|UserSignInLog whereUserId(mixed $value)
 * @method static Builder<static>|UserSignInLog whereIpAddress(mixed $value)
 * @method static Builder<static>|UserSignInLog whereUserAgent(mixed $value)
 * @method static Builder<static>|UserSignInLog whereDeviceInfo(mixed $value)
 * @method static Builder<static>|UserSignInLog whereSignedInAt(mixed $value)
 * @method static Builder<static>|UserSignInLog whereLogtoEventId(mixed $value)
 */
class UserSignInLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'user_sign_in_logs';

    /**
     * Indicates if the model should be timestamped.
     */
    public bool $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected array $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_info',
        'signed_in_at',
        'logto_event_id',
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
        'id'           => 'integer',
        'user_id'      => 'integer',
        'device_info'  => 'array',
        'signed_in_at' => 'datetime',
    ];

    /**
     * Get the user that owns this sign-in log entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
