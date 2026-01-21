<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Exceptions\ValidationException;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * Get authenticated user profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $userDTO = $this->userService->getByIdWithRelations($user->id);

        return response()->json([
            'data' => $userDTO->toArray(),
        ]);
    }

    /**
     * Update authenticated user profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                $currentUser = $this->userService->getById($user->id);
                if ($currentUser->avatar && Storage::disk('public')->exists($currentUser->avatar)) {
                    Storage::disk('public')->delete($currentUser->avatar);
                }

                // Upload new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            $userDTO = UserDTO::fromArray(array_merge($data, ['id' => $user->id]));
            $updatedUser = $this->userService->updateProfile($user->id, $userDTO);

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => $updatedUser->toArray(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Upload avatar for authenticated user.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);

            $user = $request->user();

            // Delete old avatar if exists
            $currentUser = $this->userService->getById($user->id);
            if ($currentUser->avatar && Storage::disk('public')->exists($currentUser->avatar)) {
                Storage::disk('public')->delete($currentUser->avatar);
            }

            // Upload new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $updatedUser = $this->userService->updateAvatar($user->id, $avatarPath);

            return response()->json([
                'message' => 'Avatar uploaded successfully',
                'data' => $updatedUser->toArray(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Delete avatar for authenticated user.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $updatedUser = $this->userService->deleteAvatar($user->id);

            return response()->json([
                'message' => 'Avatar deleted successfully',
                'data' => $updatedUser->toArray(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
