<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int         $id
 * @property int         $user_id                User whose password was changed
 * @property int|null    $changed_by_user_id     User who initiated the change (admin or self)
 * @property string      $change_type            Type: 'self_change' or 'admin_reset'
 * @property string|null $ip_address             IP address of the request
 * @property string|null $user_agent             User agent string
 * @property string|null $reason                 Reason for password reset (required for admin resets)
 * @property bool        $via_logto_api          Whether change was made via Logto API
 * @property array|null  $metadata               Additional metadata (admin info, method, etc.)
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * @property-read User   $user                   User whose password was changed
 * @property-read User   $changedBy              User who initiated the change
 */
class PasswordChangeLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_change_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata'      => 'array',
        'via_logto_api' => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Get the user whose password was changed.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who initiated the password change.
     *
     * @return BelongsTo
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    /**
     * Check if this was a self-initiated password change.
     *
     * @return bool
     */
    public function isSelfChange(): bool
    {
        return $this->change_type === 'self_change';
    }

    /**
     * Check if this was an admin-initiated password reset.
     *
     * @return bool
     */
    public function isAdminReset(): bool
    {
        return $this->change_type === 'admin_reset';
    }

    /**
     * Get a formatted description of the password change event.
     *
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->isSelfChange()) {
            return 'User changed their own password';
        }

        if ($this->isAdminReset() && $this->changedBy) {
            return sprintf(
                'Password reset by %s (%s)',
                $this->changedBy->name,
                $this->changedBy->email,
            );
        }

        return 'Password change';
    }
}
