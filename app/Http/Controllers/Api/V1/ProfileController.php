<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\UserDTO;
use App\Exceptions\ValidationException;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\Storage\StorageService;
use App\Support\Validation\AvatarValidation;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends ApiController
{
    public function __construct(
        protected UserService $userService,
        protected StorageService $storageService
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
                $currentUser = $this->userService->getById($user->id);
                if ($currentUser->avatar) {
                    $this->storageService->delete($currentUser->avatar, config('constants.uploads.avatar_disk'));
                }
                $avatarPath = $this->storageService->storeAvatar($request->file('avatar'), $user->id);
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
                'avatar' => AvatarValidation::requiredRules(),
            ]);

            $user = $request->user();
            $currentUser = $this->userService->getById($user->id);
            if ($currentUser->avatar) {
                $this->storageService->delete($currentUser->avatar, config('constants.uploads.avatar_disk'));
            }
            $avatarPath = $this->storageService->storeAvatar($request->file('avatar'), $user->id);
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
