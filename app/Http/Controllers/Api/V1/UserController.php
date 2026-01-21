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

/**
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints (Admin only)"
 * )
 */
class UserController extends ApiController
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name or email",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/UserResource")),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        if ($search) {
            $users = $this->userService->search($search, $perPage);
            return $this->resourcePaginatedResponse(
                UserResource::collection($users->items()),
                $users,
                'Users retrieved successfully'
            );
        }

        $users = $this->userService->getAll(['id', 'name', 'email', 'avatar', 'created_at']);
        
        // Load roles for all users
        $users->load('roles');
        
        return $this->successResponse(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     summary="Create a new user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","email","password","password_confirmation"},
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="password123"),
     *                 @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *                 @OA\Property(property="avatar", type="string", format="binary", description="User avatar image (max 2MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     )
     * )
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            $userDTO = UserDTO::fromArray($data);
            $user = $this->userService->create($userDTO);
            $userModel = app(\App\Models\User::class)->findOrFail($user->id);

            return $this->successResponse(
                new UserResource($userModel->load('roles', 'permissions')),
                'User created successfully',
                201
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Get user by ID",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getByIdWithRelations($id);
            $userModel = app(\App\Models\User::class)->findOrFail($id);

            return $this->successResponse(
                new UserResource($userModel->load('roles', 'permissions')),
                'User retrieved successfully'
            );
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     summary="Update user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                $user = $this->userService->getById($id);
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Upload new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
            }

            $userDTO = UserDTO::fromArray(array_merge($data, ['id' => $id]));
            $user = $this->userService->update($id, $userDTO);
            $userModel = app(\App\Models\User::class)->findOrFail($id);

            return $this->successResponse(
                new UserResource($userModel->load('roles', 'permissions')),
                'User updated successfully'
            );
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     summary="Delete user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Delete avatar if exists
            $user = $this->userService->getById($id);
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $this->userService->delete($id);

            return $this->successResponse(null, 'User deleted successfully');
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/{id}/avatar",
     *     summary="Upload avatar for user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
    public function uploadAvatar(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);

            // Delete old avatar if exists
            $user = $this->userService->getById($id);
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Upload new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user = $this->userService->updateAvatar($id, $avatarPath);
            $userModel = app(\App\Models\User::class)->findOrFail($id);

            return $this->successResponse(
                new UserResource($userModel->load('roles', 'permissions')),
                'Avatar uploaded successfully'
            );
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}/avatar",
     *     summary="Delete avatar for user",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
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
    public function deleteAvatar(int $id): JsonResponse
    {
        try {
            $user = $this->userService->deleteAvatar($id);
            $userModel = app(\App\Models\User::class)->findOrFail($id);

            return $this->successResponse(
                new UserResource($userModel->load('roles', 'permissions')),
                'Avatar deleted successfully'
            );
        } catch (ResourceNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }
}
