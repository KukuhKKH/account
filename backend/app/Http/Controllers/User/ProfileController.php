<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\AbstractController;
use App\Http\Requests\User\ChangeOwnPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Audit\AuditContextFactory;
use App\Services\User\UserService;
use Hypervel\Http\Request;
use Psr\Http\Message\ResponseInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        protected UserService         $userService,
        protected AuditContextFactory $auditContextFactory,
    ) {
    }

    /**
     * Change authenticated user's own password with current password verification.
     */
    public function changePassword(ChangeOwnPasswordRequest $request): ResponseInterface
    {
        /** @var User $actor */
        $actor           = $request->user();
        $context         = $this->auditContextFactory->fromRequest($request);
        $currentPassword = (string) $request->input('current_password');
        $newPassword     = (string) $request->input('new_password');

        $this->userService->changeOwnPassword(
            user:            $actor,
            currentPassword: $currentPassword,
            newPassword:     $newPassword,
            context:         $context,
        );

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi akun Anda berhasil diperbarui.',
        ]);
    }

    /**
     * Update authenticated user's own profile info.
     */
    public function updateProfile(Request $request): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->user();
        $name  = $request->input('name');
        $phone = $request->input('phone');

        if (is_string($name) && trim($name) !== '') {
            $actor->name = trim($name);
        }

        if (is_string($phone)) {
            $actor->phone = trim($phone);
        }

        $actor->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil akun berhasil diperbarui.',
            'user'    => new UserResource($actor),
        ]);
    }
}
