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

/**
 * @OA\Tag(
 *     name="Profile",
 *     description="Profile management endpoints"
 * )
 */
class ProfileController extends ApiController
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/profile",
     *     summary="Get authenticated user profile",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     )
     * )
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $userDTO = $this->userService->getByIdWithRelations($user->id);
        $userModel = app(\App\Models\User::class)->findOrFail($user->id);

        return $this->successResponse(
            new UserResource($userModel->load('roles', 'permissions')),
            'Profile retrieved successfully'
        );
    }

    /**
     * @OA\Put(
     *     path="/api/v1/profile",
     *     summary="Update authenticated user profile",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="John Updated"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *                 @OA\Property(property="avatar", type="string", format="binary", description="User avatar image (max 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     )
     * )
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
            $userModel = app(\App\Models\User::class)->findOrFail($user->id);

            return $this->successResponse(
                new UserResource($userModel->load('roles', 'permissions')),
                'Profile updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/profile/avatar",
     *     summary="Upload avatar for authenticated user",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="avatar", type="string", format="binary", description="User avatar image (max 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avatar uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avatar uploaded successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     )
     * )
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
            $userModel = app(\App\Models\User::class)->findOrFail($user->id);

            return $this->successResponse(
                new UserResource($userModel->load('roles', 'permissions')),
                'Avatar uploaded successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/profile/avatar",
     *     summary="Delete avatar for authenticated user",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Avatar deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avatar deleted successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     )
     * )
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $updatedUser = $this->userService->deleteAvatar($user->id);
            $userModel = app(\App\Models\User::class)->findOrFail($user->id);

            return $this->successResponse(
                new UserResource($userModel->load('roles', 'permissions')),
                'Avatar deleted successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
