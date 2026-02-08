<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\UserDTO;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\Storage\StorageService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function __construct(
        protected UserService $userService,
        protected StorageService $storageService
    ) {
    }

    /**
     * Get all users (always paginated).
     * Query: per_page, page, search.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', config('constants.app.default_per_page'));
        $perPage = max(1, min($perPage, config('constants.app.max_per_page', 100)));
        $search = $request->get('search');
        $columns = ['id', 'name', 'email', 'avatar', 'created_at'];

        $users = $search
            ? $this->userService->search($search, $perPage)
            : $this->userService->paginate($perPage, $columns);

        return $this->resourcePaginatedResponse(
            UserResource::collection($users->items()),
            $users,
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
            if ($request->hasFile('avatar')) {
                unset($data['avatar']);
            }
            $userDTO = UserDTO::fromArray($data);
            $user = $this->userService->create($userDTO);
            if ($request->hasFile('avatar')) {
                $avatarPath = $this->storageService->storeAvatar($request->file('avatar'), $user->id);
                $this->userService->updateAvatar($user->id, $avatarPath);
            }
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

            if ($request->hasFile('avatar')) {
                $user = $this->userService->getById($id);
                if ($user->avatar) {
                    $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
                }
                $data['avatar'] = $this->storageService->storeAvatar($request->file('avatar'), $id);
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
            $user = $this->userService->getById($id);
            if ($user->avatar) {
                $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
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

            $user = $this->userService->getById($id);
            if ($user->avatar) {
                $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
            }
            $avatarPath = $this->storageService->storeAvatar($request->file('avatar'), $id);
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
