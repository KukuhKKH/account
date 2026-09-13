<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Data\User\CreateUserData;
use App\Data\User\UpdateUserData;
use App\Data\User\UserFilterData;
use App\Enums\UserStatus;
use App\Exceptions\User\UserAccessDeniedException;
use App\Http\Controllers\AbstractController;
use App\Http\Requests\User\ChangeUserStatusRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\ListUserRequest;
use App\Http\Requests\User\ResetUserPasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\Audit\AuditContextFactory;
use App\Services\User\UserService;
use Hypervel\Http\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Class UserController
 *
 * Thin HTTP Orchestration Controller for BangLipai User Management.
 * Authentication is enforced via middleware, authorization via UserPolicy,
 * and error responses via the Central Exception Handler.
 */
class UserController extends AbstractController
{
    public function __construct(
        protected UserService         $userService,
        protected UserPolicy          $userPolicy,
        protected AuditContextFactory $auditContextFactory,
    ) {
    }

    /**
     * Display a listing of users with search, role filters, and pagination.
     */
    public function index(ListUserRequest $request): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->user();

        if (! $this->userPolicy->viewAny($actor)) {
            throw new UserAccessDeniedException('Hanya Administrator yang memiliki akses ke direktori akun.');
        }

        $filter = UserFilterData::fromArray($request->validated());
        $result = $this->userService->listUsers($filter);

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($result['items']),
            'meta'    => [
                'currentPage' => $result['page'],
                'perPage'     => $result['perPage'],
                'total'       => $result['total'],
                'lastPage'    => $result['lastPage'],
            ],
        ]);
    }

    /**
     * Store a newly created user in storage with strict security policy checks.
     */
    public function store(CreateUserRequest $request): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->user();
        $role  = (string) $request->input('role');

        if (! $this->userPolicy->create($actor, $role)) {
            throw new UserAccessDeniedException('Anda tidak memiliki izin untuk mendaftarkan pengguna dengan hak akses ini.');
        }

        $data    = CreateUserData::fromArray($request->validated());
        $context = $this->auditContextFactory->fromRequest($request);

        $newUser = $this->userService->createUser(
            data:    $data,
            actor:   $actor,
            context: $context,
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengguna baru berhasil didaftarkan ke sistem.',
            'data'    => new UserResource($newUser),
        ], 201);
    }

    /**
     * Display the specified user details.
     */
    public function show(Request $request, int $id): ResponseInterface
    {
        /** @var User $actor */
        $actor = $request->user();
        $user  = $this->userService->getUserById($id);

        if (! $this->userPolicy->view($actor, $user)) {
            throw new UserAccessDeniedException('Anda tidak memiliki wewenang untuk melihat akun ini.');
        }

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, int $id): ResponseInterface
    {
        /** @var User $actor */
        $actor      = $request->user();
        $targetUser = $this->userService->getUserById($id);
        $role       = $request->input('role');

        if (! $this->userPolicy->update($actor, $targetUser, is_string($role) ? $role : null)) {
            throw new UserAccessDeniedException('Anda tidak memiliki hak akses untuk mengubah data pengguna ini.');
        }

        $data    = UpdateUserData::fromArray($request->validated());
        $context = $this->auditContextFactory->fromRequest($request);

        $updatedUser = $this->userService->updateUser(
            target:  $targetUser,
            data:    $data,
            actor:   $actor,
            context: $context,
        );

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diperbarui.',
            'data'    => new UserResource($updatedUser),
        ]);
    }

    /**
     * Remove the specified user from storage (soft-delete).
     */
    public function destroy(Request $request, int $id): ResponseInterface
    {
        /** @var User $actor */
        $actor      = $request->user();
        $targetUser = $this->userService->getUserById($id);

        if (! $this->userPolicy->delete($actor, $targetUser)) {
            throw new UserAccessDeniedException('Anda tidak memiliki izin untuk menghapus akun pengguna ini.');
        }

        $context = $this->auditContextFactory->fromRequest($request);

        $this->userService->deleteUser(
            target:  $targetUser,
            actor:   $actor,
            context: $context,
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus dari sistem dengan riwayat audit tetap terjaga.',
        ]);
    }

    /**
     * Explicitly update user account status (Active or Suspended) with idempotent state assignment.
     */
    public function changeStatus(ChangeUserStatusRequest $request, int $id): ResponseInterface
    {
        /** @var User $actor */
        $actor      = $request->user();
        $targetUser = $this->userService->getUserById($id);

        if (! $this->userPolicy->changeStatus($actor, $targetUser)) {
            throw new UserAccessDeniedException('Anda tidak memiliki izin untuk mengubah status akun pengguna ini.');
        }

        $status  = UserStatus::from((string) $request->input('status'));
        $reason  = $request->input('reason');
        $context = $this->auditContextFactory->fromRequest($request);

        $user = $this->userService->changeStatus(
            target:  $targetUser,
            status:  $status,
            actor:   $actor,
            context: $context,
            reason:  is_string($reason) ? $reason : null,
        );

        $statusText = $status->isSuspended() ? 'ditangguhkan (suspended)' : 'diaktifkan kembali';

        return response()->json([
            'success' => true,
            'message' => "Akun pengguna berhasil {$statusText}.",
            'data'    => new UserResource($user),
        ]);
    }

    /**
     * Reset the specified user's password via administrator action.
     */
    public function resetPassword(ResetUserPasswordRequest $request, int $id): ResponseInterface
    {
        /** @var User $actor */
        $actor      = $request->user();
        $targetUser = $this->userService->getUserById($id);

        if (! $this->userPolicy->resetPassword($actor, $targetUser)) {
            throw new UserAccessDeniedException('Anda tidak memiliki wewenang untuk mengatur ulang kata sandi akun ini.');
        }

        $newPassword = (string) $request->input('password');
        $reason      = $request->input('reason');
        $context     = $this->auditContextFactory->fromRequest($request);

        $updatedUser = $this->userService->resetUserPassword(
            target:      $targetUser,
            newPassword: $newPassword,
            actor:       $actor,
            context:     $context,
            reason:      is_string($reason) ? $reason : null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi pengguna berhasil diubah.',
            'data'    => new UserResource($updatedUser),
        ]);
    }
}
