<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PasswordChangeLog;
use Hypervel\Http\Resources\Json\JsonResource;

/**
 * Class PasswordChangeLogResource
 *
 * @property-read PasswordChangeLog $resource
 */
class PasswordChangeLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param mixed $request
     * @return array<string, mixed>
     */
    public function toArray($request = null): array
    {
        /** @var PasswordChangeLog $log */
        $log = $this->resource;

        $changeTypeValue = $log->change_type->value;
        $changeTypeLabel = $log->change_type->label();

        $userRoles = $log->user->roles->pluck('role')->all();
        $userRole  = ! empty($userRoles) ? $userRoles[0] : 'User';

        $changedByRoles = $log->changedBy?->roles->pluck('role')->all() ?? [];
        $changedByRole  = ! empty($changedByRoles) ? $changedByRoles[0] : null;

        return [
            'id'              => (string) $log->id,
            'userId'          => (string) $log->user_id,
            'userName'        => $log->user->name,
            'userEmail'       => $log->user->email,
            'userRole'        => $userRole,
            'changedByUserId' => $log->changed_by_user_id ? (string) $log->changed_by_user_id : null,
            'changedByName'   => $log->changedBy?->name,
            'changedByEmail'  => $log->changedBy?->email,
            'changedByRole'   => $changedByRole,
            'changeType'      => $changeTypeValue,
            'changeTypeLabel' => $changeTypeLabel,
            'isSelf'          => $log->isSelfChange(),
            'isAdmin'         => $log->isAdminReset(),
            'isSystem'        => $log->isSystemReset(),
            'ipAddress'       => $log->ip_address,
            'userAgent'       => $log->user_agent,
            'reason'          => $log->reason,
            'description'     => $log->getDescription(),
            'viaLogtoApi'     => $log->via_logto_api,
            'metadata'        => $log->metadata,
            'createdAt'       => $log->created_at?->toIso8601String(),
            'timeAgo'         => $log->created_at?->diffForHumans() ?? 'Baru saja',
        ];
    }
}
