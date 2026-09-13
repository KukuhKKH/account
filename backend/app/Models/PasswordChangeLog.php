<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PasswordChangeType;
use Carbon\Carbon;
use Hypervel\Database\Eloquent\Builder;
use Hypervel\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PasswordChangeLog
 *
 * @property int                       $id
 * @property int                       $user_id
 * @property int|null                  $changed_by_user_id
 * @property PasswordChangeType        $change_type
 * @property string|null               $ip_address
 * @property string|null               $user_agent
 * @property string|null               $reason
 * @property bool                      $via_logto_api
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null               $created_at
 * @property Carbon|null               $updated_at
 *
 * @property-read User                 $user
 * @property-read User|null            $changedBy
 *
 * @method static Builder<static>|PasswordChangeLog newModelQuery()
 * @method static Builder<static>|PasswordChangeLog newQuery()
 * @method static Builder<static>|PasswordChangeLog query()
 * @method static Builder<static>|PasswordChangeLog whereId(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereUserId(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereChangedByUserId(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereChangeType(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereIpAddress(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereUserAgent(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereReason(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereViaLogtoApi(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereMetadata(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereCreatedAt(mixed $value)
 * @method static Builder<static>|PasswordChangeLog whereUpdatedAt(mixed $value)
 */
class PasswordChangeLog extends Model
{
    public const string CHANGE_TYPE_SELF         = 'self_change';
    public const string CHANGE_TYPE_ADMIN_RESET  = 'admin_reset';
    public const string CHANGE_TYPE_SYSTEM_RESET = 'system_reset';
    public const string CHANGE_TYPE_WEBHOOK_SYNC = 'webhook_sync';

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'password_change_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected array $fillable = [
        'user_id',
        'changed_by_user_id',
        'change_type',
        'ip_address',
        'user_agent',
        'reason',
        'via_logto_api',
        'metadata',
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
        'id'                 => 'integer',
        'user_id'            => 'integer',
        'changed_by_user_id' => 'integer',
        'change_type'        => PasswordChangeType::class,
        'via_logto_api'      => 'boolean',
        'metadata'           => 'array',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /**
     * Get the user whose password was changed.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the user (admin/actor) who initiated the password change.
     *
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id', 'id');
    }

    /**
     * Check if this was a self-initiated password change.
     *
     * @return bool
     */
    public function isSelfChange(): bool
    {
        return $this->change_type === PasswordChangeType::SelfChange;
    }

    /**
     * Check if this was an admin-initiated password reset.
     *
     * @return bool
     */
    public function isAdminReset(): bool
    {
        return $this->change_type === PasswordChangeType::AdminReset;
    }

    /**
     * Check if this was a system-initiated password reset.
     *
     * @return bool
     */
    public function isSystemReset(): bool
    {
        return $this->change_type === PasswordChangeType::SystemReset;
    }

    /**
     * Get a human-readable formatted description of the password change event.
     *
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->isSelfChange()) {
            return 'Pengguna memperbarui kata sandi mandiri via profil';
        }

        if ($this->isAdminReset() && $this->changedBy !== null) {
            return sprintf(
                'Kata sandi diatur ulang oleh Administrator %s (%s)',
                $this->changedBy->name,
                $this->changedBy->email,
            );
        }

        if ($this->isSystemReset()) {
            return 'Pengaturan ulang kata sandi oleh sistem otomatis';
        }

        return 'Perubahan kata sandi akun';
    }
}
