<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Profile\UpdatePasswordRequest;
use App\Http\Requests\Web\Profile\UpdateProfileRequest;
use App\Services\Storage\StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected StorageService $storageService
    ) {
    }

    public function show(): View
    {
        $user = auth()->user();

        return view('profile.show', [
            'user' => $user,
            'activeTab' => session('active_profile_tab', 'profile'),
            'primaryRole' => $user->roles()->pluck('name')->first(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $updateData = [
            'name' => trim($request->string('name')->toString()),
        ];

        if ($request->hasFile('avatar')) {
            $disk = (string) config('constants.uploads.avatar_disk', 'public');
            $path = $this->storageService->storeAvatar($request->file('avatar'), (int) $user->id, $disk);
            $oldAvatar = $user->avatar;

            $updateData['avatar'] = $path;
            if (is_string($oldAvatar) && $oldAvatar !== '') {
                $this->storageService->delete($oldAvatar, $disk);
            }
        }

        $user->update($updateData);

        return redirect()
            ->route('profile.show')
            ->with('active_profile_tab', 'profile')
            ->with('status', 'Cập nhật thông tin cá nhân thành công.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => $request->string('password')->toString(),
        ]);

        return redirect()
            ->route('profile.show')
            ->with('active_profile_tab', 'password')
            ->with('status', 'Đổi mật khẩu thành công.');
    }
}
