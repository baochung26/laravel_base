<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Profile\UpdatePasswordRequest;
use App\Http\Requests\Web\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
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
            $path = $request->file('avatar')->store('avatars', 'public');
            $oldAvatar = $user->avatar;

            $updateData['avatar'] = $path;
            if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                Storage::disk('public')->delete($oldAvatar);
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
