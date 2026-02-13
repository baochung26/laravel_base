<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Profile\UpdatePasswordRequest;
use App\Http\Requests\Web\Profile\UpdateProfileRequest;
use App\Services\UserService;
use App\DTOs\UserDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    public function show(): View
    {
        $user = auth()->user();

        return view('profile.show', [
            'user' => $user,
            'activeTab' => session('active_profile_tab', 'profile'),
            'primaryRole' => $this->userService->getPrimaryRoleName($user->id),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $userDTO = UserDTO::fromArray([
            'id' => $user->id,
            'name' => trim((string) $data['name']),
            'email' => $user->email,
        ]);

        $this->userService->updateProfileWithAvatar($user->id, $userDTO, $request->file('avatar'));

        return redirect()
            ->route('profile.show')
            ->with('active_profile_tab', 'profile')
            ->with('status', __('messages.success.profile_updated'));
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        try {
            $this->userService->changePassword(
                $user->id,
                $request->string('current_password')->toString(),
                $request->string('password')->toString()
            );
        } catch (\App\Exceptions\ValidationException $exception) {
            return redirect()
                ->route('profile.show')
                ->with('active_profile_tab', 'password')
                ->withErrors(['current_password' => $exception->getMessage()], 'passwordUpdate');
        }

        return redirect()
            ->route('profile.show')
            ->with('active_profile_tab', 'password')
            ->with('status', __('messages.success.password_changed'));
    }
}
