<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\UserDTO;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends ApiController
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * Get all users.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', config('constants.app.default_per_page'));
        $search = $request->get('search');

        if ($search) {
            $users = $this->userService->search($search, $perPage);
            return $this->resourcePaginatedResponse(
                UserResource::collection($users->items()),
                $users,
                'Users retrieved successfully'
            );
        }

        // getAll() now automatically eager loads roles
        $users = $this->userService->getAll(['id', 'name', 'email', 'avatar', 'created_at']);
        
        return $this->successResponse(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Create a new user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store(
                    config('constants.uploads.avatar_dir'),
                    config('constants.uploads.avatar_disk')
                );
                $data['avatar'] = $avatarPath;
            }

            $userDTO = UserDTO::fromArray($data);
            $user = $this->userService->create($userDTO);
            // Get model with relations for Resource
            $userModel = $this->userService->getModelByIdWithRelations($user->id);

            return $this->successResponse(
                new UserResource($userModel),
                'User created successfully',
                201
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Get user by ID.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $userModel = $this->userService->getModelByIdWithRelations($id);

            return $this->successResponse(
                new UserResource($userModel),
                'User retrieved successfully'
            );
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * Update user.
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                $user = $this->userService->getById($id);
                if ($user->avatar && Storage::disk(config('constants.uploads.avatar_disk'))->exists($user->avatar)) {
                    Storage::disk(config('constants.uploads.avatar_disk'))->delete($user->avatar);
                }

                // Upload new avatar
                $avatarPath = $request->file('avatar')->store(
                    config('constants.uploads.avatar_dir'),
                    config('constants.uploads.avatar_disk')
                );
                $data['avatar'] = $avatarPath;
            }

            $userDTO = UserDTO::fromArray(array_merge($data, ['id' => $id]));
            $this->userService->update($id, $userDTO);
            $userModel = $this->userService->getModelByIdWithRelations($id);

            return $this->successResponse(
                new UserResource($userModel),
                'User updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * Delete user.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Delete avatar if exists
            $user = $this->userService->getById($id);
            if ($user->avatar && Storage::disk(config('constants.uploads.avatar_disk'))->exists($user->avatar)) {
                Storage::disk(config('constants.uploads.avatar_disk'))->delete($user->avatar);
            }

            $this->userService->delete($id);

            return $this->successResponse(null, 'User deleted successfully');
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * Upload avatar for user.
     */
    public function uploadAvatar(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => [
                    'required',
                    'image',
                    'mimes:' . implode(',', config('constants.uploads.allowed_avatar_mimes')),
                    'max:' . config('constants.uploads.max_avatar_kb'),
                ],
            ]);

            // Delete old avatar if exists
            $user = $this->userService->getById($id);
            if ($user->avatar && Storage::disk(config('constants.uploads.avatar_disk'))->exists($user->avatar)) {
                Storage::disk(config('constants.uploads.avatar_disk'))->delete($user->avatar);
            }

            // Upload new avatar
            $avatarPath = $request->file('avatar')->store(
                config('constants.uploads.avatar_dir'),
                config('constants.uploads.avatar_disk')
            );
            $this->userService->updateAvatar($id, $avatarPath);
            $userModel = $this->userService->getModelByIdWithRelations($id);

            return $this->successResponse(
                new UserResource($userModel),
                'Avatar uploaded successfully'
            );
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * Delete avatar for user.
     */
    public function deleteAvatar(int $id): JsonResponse
    {
        try {
            $this->userService->deleteAvatar($id);
            $userModel = $this->userService->getModelByIdWithRelations($id);

            return $this->successResponse(
                new UserResource($userModel),
                'Avatar deleted successfully'
            );
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }
}
