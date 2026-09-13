<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Hypervel\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 *
 * Safe API representation for User model.
 * Guarantees zero leakage of sensitive credentials, password hashes, or internal tokens.
 *
 * @property-read User $resource
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param mixed $request
     * @return array<string, mixed>
     */
    public function toArray($request = null): array
    {
        /** @var User $user */
        $user = $this->resource;

        $roles = $user->roles->pluck('role')->all();

        if (empty($roles)) {
            $roles = ['User'];
        }

        $primaryRole = in_array('Superadmin', $roles, true)
            ? 'Superadmin'
            : (in_array('Admin Account', $roles, true) || in_array('Admin', $roles, true) ? 'Admin Account' : 'User');

        $statusValue = $user->status->value;
        $isSuspended = $user->status->isSuspended();
        $lastLoginAt = $user->last_login_at?->toIso8601String();
        $createdAt   = $user->created_at?->toIso8601String();

        return [
            'id'          => (string) $user->id,
            'logtoId'     => $user->logto_id,
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $primaryRole,
            'roles'       => $roles,
            'status'      => $statusValue,
            'isSuspended' => $isSuspended,
            'phone'       => $user->phone,
            'address'     => $user->address,
            'avatar'      => $user->avatar,
            'lastActive'  => $lastLoginAt ? Carbon::parse($lastLoginAt)->diffForHumans() : 'Belum pernah',
            'lastLoginAt' => $lastLoginAt,
            'authMethod'  => $user->logto_id ? 'SSO Passkey' : 'BFF Session',
            'customData'  => $user->custom_data,
            'createdAt'   => $createdAt,
        ];
    }
}
