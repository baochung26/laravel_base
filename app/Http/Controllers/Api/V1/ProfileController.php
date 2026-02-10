<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\UserDTO;
use App\Exceptions\ValidationException;
use App\Http\Requests\Profile\UploadAvatarRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $user = $this->userService->getModelByIdWithRelations($request->user()->id);

        return $this->successResponse(
            new UserResource($user),
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
            unset($data['avatar']);
            $userDTO = UserDTO::fromArray(array_merge($data, ['id' => $user->id]));
            $userModel = $this->userService->updateProfileWithAvatar($user->id, $userDTO, $request->file('avatar'));

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
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->userService->setAvatarFromFile($user->id, $request->file('avatar'));
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
