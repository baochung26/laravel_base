<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use App\DTOs\UserDTO;
use App\Exceptions\ValidationException;
use App\Http\Requests\Web\Dashboard\UserIndexRequest;
use App\Http\Requests\Web\Dashboard\UserStatusRequest;
use App\Http\Requests\Web\Dashboard\UserStoreRequest;
use App\Http\Requests\Web\Dashboard\UserUpdateRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardUserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    public function create(): View
    {
        return view('dashboard.users.create', [
            'roles' => $this->userService->getRoleNames(),
        ]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $userDTO = UserDTO::fromArray($validated);
            $this->userService->createDashboardUser($userDTO, $validated['role'], $validated['status']);

            return redirect()->route('dashboard.users.index')->with('status', __('messages.success.dashboard_user_created'));
        } catch (ValidationException $exception) {
            return back()
                ->withErrors(['email' => $exception->getMessage()])
                ->withInput();
        }
    }

    public function index(UserIndexRequest $request): View
    {
        $filters = $this->userService->normalizeDashboardFilters($request->validated());
        $users = $this->userService->paginateForDashboard($filters, 10)->withQueryString();
        $stats = $this->userService->getDashboardStats();

        return view('dashboard.users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => $this->userService->getRoleNames(),
            'stats' => $stats,
        ]);
    }

    public function edit(User $user): View
    {
        return view('dashboard.users.edit', [
            'targetUser' => $this->userService->getModelByIdWithRelations($user->id),
            'roles' => $this->userService->getRoleNames(),
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $userDTO = UserDTO::fromArray($validated);
            $this->userService->updateDashboardUser($user->id, $userDTO, $validated['role'] ?? null);

            return $this->redirectBackToUsersList($request, __('messages.success.dashboard_user_updated'));
        } catch (ValidationException $exception) {
            return $this->redirectBackToUsersList($request)
                ->withErrors(['email' => $exception->getMessage()]);
        }
    }

    public function updateStatus(UserStatusRequest $request, User $user): RedirectResponse
    {
        try {
            $actor = $request->user();
            $validated = $request->validated();

            $this->userService->updateDashboardUserStatus($actor->id, $user->id, $validated['status']);

            return $this->redirectBackToUsersList($request, __('messages.success.dashboard_user_status_updated'));
        } catch (ValidationException $exception) {
            return $this->redirectBackToUsersList($request)->withErrors([
                'status' => $exception->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        try {
            $actor = $request->user();
            $this->userService->deleteDashboardUser($actor->id, $user->id);

            return $this->redirectBackToUsersList($request, __('messages.success.dashboard_user_deleted'));
        } catch (ValidationException $exception) {
            return $this->redirectBackToUsersList($request)->withErrors([
                'delete' => $exception->getMessage(),
            ]);
        }
    }

    protected function redirectBackToUsersList(Request $request, ?string $status = null): RedirectResponse
    {
        $fallback = route('dashboard.users.index');
        $target = $request->headers->get('referer', $fallback);

        if (! is_string($target) || $target === '') {
            $target = $fallback;
        }

        $redirect = redirect()->to($target);

        if ($status !== null) {
            $redirect->with('status', $status);
        }

        return $redirect;
    }
}
