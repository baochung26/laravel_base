<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\UserDTO;
use App\Exceptions\ValidationException;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends ApiController
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
        $userModel = $this->userService->getModelByIdWithRelations($user->id);

        return $this->successResponse(
            new UserResource($userModel),
            'Profile retrieved successfully'
        );
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
            $this->userService->updateProfile($user->id, $userDTO);
            $userModel = $this->userService->getModelByIdWithRelations($user->id);

            return $this->successResponse(
                new UserResource($userModel),
                'Profile updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
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
            $this->userService->updateAvatar($user->id, $avatarPath);
            $userModel = $this->userService->getModelByIdWithRelations($user->id);

            return $this->successResponse(
                new UserResource($userModel),
                'Avatar uploaded successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete avatar for authenticated user.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->userService->deleteAvatar($user->id);
            $userModel = $this->userService->getModelByIdWithRelations($user->id);

            return $this->successResponse(
                new UserResource($userModel),
                'Avatar deleted successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
