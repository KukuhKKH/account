<?php

namespace App\Http\Controllers;

use App\Data\CreateUserData;
use App\Data\UpdateUserData;
use App\Http\Requests\AdminResetPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\PasswordChangeService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserService           $userService,
        protected PasswordChangeService $passwordChangeService,
    ) {}

    /**
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = request()->only(['search', 'role', 'order_by', 'order_direction']);
        $users   = $this->userService->listUsers($filters, currentUser: auth()->user());

        $users->getCollection()
            ->load('roles');

        return Inertia::render('Users/Index', [
            'users'   => $users,
            'filters' => $filters,
        ]);
    }

    /**
     * @return Response
     */
    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create');
    }

    /**
     * @param StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $data = CreateUserData::from($request->validated());
            $user = $this->userService->createUser($data);

            return redirect()->route('users.show', $user)
                ->with('success', 'User berhasil dibuat.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membuat user: ' . $e->getMessage());
        }
    }

    /**
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->load(['signInLogs', 'roles']);
        $signInLogs = $this->userService->getUserSignInHistory($user, 20);

        return Inertia::render('Users/Show', [
            'user'       => $user,
            'signInLogs' => $signInLogs,
        ]);
    }

    /**
     * @param User $user
     * @return Response
     */
    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load('roles');

        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * @param UpdateUserRequest $request
     * @param User              $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $data = UpdateUserData::from($request->validated());
            $this->userService->updateUser($user, $data);

            return redirect()->route('users.show', $user)
                ->with('success', 'User berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memperbarui user: ' . $e->getMessage());
        }
    }

    /**
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        try {
            $this->userService->deleteUser($user);

            return redirect()->route('users.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * @return Response
     */
    public function profile(): Response
    {
        $user = auth()->user();
        $user->load('roles');

        $signInLogs         = $this->userService->getUserSignInHistory($user);
        $passwordChangeLogs = $this->passwordChangeService->getPasswordChangeHistory($user, 5);

        return Inertia::render('Profile/Show', [
            'user'               => $user,
            'signInLogs'         => $signInLogs,
            'passwordChangeLogs' => $passwordChangeLogs,
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function requestPasswordReset(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->logto_id) {
            return back()->with('error', 'Akun Anda tidak terhubung dengan sistem. Hubungi administrator.');
        }

        try {
            $this->passwordChangeService->sendPasswordResetEmail(
                $user,
                $request->ip(),
                $request->userAgent(),
            );

            return back()->with('success', 'Email reset password telah dikirim. Silakan cek inbox Anda.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'currentPassword' => 'required|string',
            'newPassword'     => 'required|string|min:8|different:currentPassword',
            'confirmPassword' => 'required|string|same:newPassword',
        ], [
            'newPassword.different' => 'Password baru harus berbeda dengan password saat ini.',
            'confirmPassword.same'  => 'Konfirmasi password tidak cocok.',
            'newPassword.min'       => 'Password minimal 8 karakter.',
        ]);

        $user = auth()->user();

        if (!$user->logto_id) {
            return back()->with('error', 'Akun Anda tidak terhubung dengan sistem. Hubungi administrator.');
        }

        try {
            $this->passwordChangeService->changeUserPassword(
                $user,
                $request->input('currentPassword'),
                $request->input('newPassword'),
                $request->ip(),
                $request->userAgent(),
            );

            return back()->with('success', 'Password berhasil diubah. Gunakan password baru untuk login selanjutnya.');
        } catch (Exception $e) {
            return back()->withErrors(['currentPassword' => $e->getMessage()]);
        }
    }

    /**
     * @param User $user
     * @return Response
     */
    public function showResetPasswordForm(User $user): Response
    {
        $this->authorize('update', $user);

        if (!auth()->user()->isSuperadmin() && !auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin dan Superadmin yang dapat mereset password user.');
        }

        if (!$user->logto_id) {
            abort(400, 'User tidak memiliki Logto ID. Password tidak dapat direset.');
        }

        $user->load('roles');
        $passwordChangeLogs = $this->passwordChangeService->getPasswordChangeHistory($user, 10);

        return Inertia::render('Users/ResetPassword', [
            'user'               => $user,
            'passwordChangeLogs' => $passwordChangeLogs,
        ]);
    }

    /**
     * @param AdminResetPasswordRequest $request
     * @param User                      $user
     * @return RedirectResponse
     */
    public function resetUserPassword(AdminResetPasswordRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if (!auth()->user()->isSuperadmin() && !auth()->user()->isAdmin()) {
            abort(403, 'Hanya Admin dan Superadmin yang dapat mereset password user.');
        }

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Gunakan fitur "Change Password" di profil untuk mengubah password Anda sendiri.');
        }

        try {
            $this->passwordChangeService->resetUserPassword(
                $user,
                auth()->user(),
                $request->validated('password'),
                $request->validated('reason'),
                $request->ip(),
                $request->userAgent(),
            );

            return redirect()->route('users.show', $user)
                ->with('success', 'Password user berhasil direset. User dapat login dengan password baru.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * @return Response
     */
    public function dashboard(): Response
    {
        $statistics    = $this->userService->getUserStatistics();
        $recentSignIns = $this->userService->getRecentSignIns();

        $recentSignIns->load('roles');

        return Inertia::render('Dashboard', [
            'statistics'    => $statistics,
            'recentSignIns' => $recentSignIns,
        ]);
    }
}
