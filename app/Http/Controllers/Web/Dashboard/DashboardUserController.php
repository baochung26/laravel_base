<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use App\Helpers\PasswordRules;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class DashboardUserController extends Controller
{
    public function create(Request $request): View
    {
        return view('dashboard.users-create', [
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', PasswordRules::standard()],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $newUser = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verified_at' => $validated['status'] === 'active' ? now() : null,
        ]);

        $newUser->syncRoles([$validated['role']]);

        return redirect()->route('dashboard.users.index')->with('status', 'Tạo người dùng thành công.');
    }

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'role' => trim((string) $request->query('role', '')),
            'status' => trim((string) $request->query('status', '')),
            'sort_by' => trim((string) $request->query('sort_by', 'created_at')),
            'sort_dir' => trim((string) $request->query('sort_dir', 'desc')),
        ];

        $allowedSortBy = ['created_at', 'name', 'email'];
        if (! in_array($filters['sort_by'], $allowedSortBy, true)) {
            $filters['sort_by'] = 'created_at';
        }

        $filters['sort_dir'] = $filters['sort_dir'] === 'asc' ? 'asc' : 'desc';
        $filters['status'] = in_array($filters['status'], ['active', 'inactive'], true)
            ? $filters['status']
            : '';

        $usersQuery = User::query()
            ->with('roles')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $keyword = $filters['q'];
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery
                        ->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%');
                });
            })
            ->when($filters['role'] !== '', function ($query) use ($filters) {
                $query->whereHas('roles', function ($roleQuery) use ($filters) {
                    $roleQuery->where('name', $filters['role']);
                });
            })
            ->when($filters['status'] === 'active', function ($query) {
                $query->whereNotNull('email_verified_at');
            })
            ->when($filters['status'] === 'inactive', function ($query) {
                $query->whereNull('email_verified_at');
            })
            ->orderBy($filters['sort_by'], $filters['sort_dir']);

        $users = $usersQuery->paginate(10)->withQueryString();

        $stats = [
            'total' => User::query()->count(),
            'active' => User::query()->whereNotNull('email_verified_at')->count(),
        ];
        $stats['inactive'] = max($stats['total'] - $stats['active'], 0);
        $stats['admin'] = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->count();

        return view('dashboard.users', [
            'users' => $users,
            'filters' => $filters,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'stats' => $stats,
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        return view('dashboard.users-edit', [
            'targetUser' => $user->load('roles'),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['nullable', 'string', Rule::exists('roles', 'name')],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return $this->redirectBackToUsersList($request, 'Cập nhật người dùng thành công.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if ($actor->id === $user->id) {
            return $this->redirectBackToUsersList($request)->withErrors([
                'status' => 'Bạn không thể tự thay đổi trạng thái của chính mình.',
            ]);
        }

        if ($validated['status'] === 'inactive') {
            $user->forceFill(['email_verified_at' => null])->save();
        } else {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $this->redirectBackToUsersList($request, 'Cập nhật trạng thái người dùng thành công.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if ($actor->id === $user->id) {
            return $this->redirectBackToUsersList($request)->withErrors([
                'delete' => 'Bạn không thể tự xóa chính mình.',
            ]);
        }

        $user->delete();

        return $this->redirectBackToUsersList($request, 'Xóa người dùng thành công.');
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
