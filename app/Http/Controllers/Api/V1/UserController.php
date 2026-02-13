<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\UserDTO;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Support\Validation\AvatarValidation;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function __construct(
        protected UserService $userService
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

        $users = $search
            ? $this->userService->search($search, $perPage)
            : $this->userService->paginate($perPage);

        return $this->resourcePaginatedResponse(
            UserResource::collection($users->items()),
            $users,
            __('messages.success.users_retrieved')
        );
    }

    /**
     * Create a new user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            unset($data['avatar']);
            $userDTO = UserDTO::fromArray($data);
            $user = $this->userService->createWithAvatar($userDTO, $request->file('avatar'));

        return $this->successResponse(
            new UserResource($user),
            __('messages.success.user_created'),
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
            $user = $this->userService->getModelByIdWithRelations($id);

        return $this->successResponse(
            new UserResource($user),
            __('messages.success.user_retrieved')
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
            unset($data['avatar']);
            $userDTO = UserDTO::fromArray(array_merge($data, ['id' => $id]));
            $user = $this->userService->updateWithAvatar($id, $userDTO, $request->file('avatar'));

        return $this->successResponse(
            new UserResource($user),
            __('messages.success.user_updated')
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
            $this->userService->delete($id);

        return $this->successResponse(null, __('messages.success.user_deleted'));
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
                'avatar' => AvatarValidation::requiredRules(),
            ]);
            $this->userService->setAvatarFromFile($id, $request->file('avatar'));
            $user = $this->userService->getModelByIdWithRelations($id);

        return $this->successResponse(
            new UserResource($user),
            __('messages.success.avatar_uploaded')
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
            $user = $this->userService->getModelByIdWithRelations($id);

        return $this->successResponse(
            new UserResource($user),
            __('messages.success.avatar_deleted')
        );
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }
}
