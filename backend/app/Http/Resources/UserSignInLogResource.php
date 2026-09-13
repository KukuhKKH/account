<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UserSignInLog;
use Hypervel\Http\Resources\Json\JsonResource;

/**
 * Class UserSignInLogResource
 *
 * @property-read UserSignInLog $resource
 */
class UserSignInLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param mixed $request
     * @return array<string, mixed>
     */
    public function toArray($request = null): array
    {
        /** @var UserSignInLog $log */
        $log = $this->resource;

        $userRoles = $log->user->roles->pluck('role')->all();
        $userRole  = ! empty($userRoles) ? $userRoles[0] : 'User';

        $browser = is_array($log->device_info) && isset($log->device_info['browser'])
            ? (string) $log->device_info['browser']
            : 'Unknown';

        $os = is_array($log->device_info) && isset($log->device_info['os'])
            ? (string) $log->device_info['os']
            : 'Unknown';

        return [
            'id'           => (string) $log->id,
            'userId'       => (string) $log->user_id,
            'userName'     => $log->user->name,
            'userEmail'    => $log->user->email,
            'userRole'     => $userRole,
            'ipAddress'    => $log->ip_address,
            'userAgent'    => $log->user_agent,
            'deviceInfo'   => $log->device_info,
            'browser'      => $browser,
            'os'           => $os,
            'signedInAt'   => $log->signed_in_at->toIso8601String(),
            'timeAgo'      => $log->signed_in_at->diffForHumans(),
            'logtoEventId' => $log->logto_event_id,
        ];
    }
}
