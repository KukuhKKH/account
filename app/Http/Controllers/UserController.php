<?php

namespace App\Http\Controllers;

use App\Data\CreateUserData;
use App\Data\UpdateUserData;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
    ) {}

    /**
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        $filters = request()->only(['search', 'role', 'order_by', 'order_direction']);
        $users   = $this->userService->listUsers($filters);

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

        $user->load('signInLogs');
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
        $user       = auth()->user();
        $signInLogs = $this->userService->getUserSignInHistory($user);

        return Inertia::render('Profile/Show', [
            'user'       => $user,
            'signInLogs' => $signInLogs,
        ]);
    }

    /**
     * @return Response
     */
    public function dashboard(): Response
    {
        $statistics    = $this->userService->getUserStatistics();
        $recentSignIns = $this->userService->getRecentSignIns();

        return Inertia::render('Dashboard', [
            'statistics'    => $statistics,
            'recentSignIns' => $recentSignIns,
        ]);
    }
}
