<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int         $id
 * @property int         $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null  $device_info
 * @property Carbon      $signed_in_at
 * @property string|null $logto_event_id
 *
 * @property-read User   $user
 *
 * @method static Builder<static> newModelQuery()
 * @method static Builder<static> newQuery()
 * @method static Builder<static> query()
 * @method static Builder<static> whereId($value)
 * @method static Builder<static> whereUserId($value)
 * @method static Builder<static> whereIpAddress($value)
 * @method static Builder<static> whereUserAgent($value)
 * @method static Builder<static> whereDeviceInfo($value)
 * @method static Builder<static> whereSignedInAt($value)
 * @method static Builder<static> whereLogtoEventId($value)
 */
class UserSignInLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_sign_in_logs';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_info',
        'signed_in_at',
        'logto_event_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'device_info'  => 'array',
            'signed_in_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this sign-in log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
