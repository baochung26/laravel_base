<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');

        if ($search) {
            $users = $this->userService->search($search, $perPage);
            return response()->json([
                'data' => $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                ],
            ]);
        }

        $users = $this->userService->getAll(['id', 'name', 'email', 'avatar', 'created_at']);
        
        return response()->json([
            'data' => $users->map(fn ($user) => UserDTO::fromModel($user)->toArray()),
        ]);
    }

    /**
     * Store a newly created user.
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

            return response()->json([
                'message' => 'User created successfully',
                'data' => $user->toArray(),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getByIdWithRelations($id);

            return response()->json([
                'data' => $user->toArray(),
            ]);
        } catch (ResourceNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified user.
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

            return response()->json([
                'message' => 'User updated successfully',
                'data' => $user->toArray(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (ResourceNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove the specified user.
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

            return response()->json([
                'message' => 'User deleted successfully',
            ]);
        } catch (ResourceNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Upload avatar for user.
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

            return response()->json([
                'message' => 'Avatar uploaded successfully',
                'data' => $user->toArray(),
            ]);
        } catch (ResourceNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Delete avatar for user.
     */
    public function deleteAvatar(int $id): JsonResponse
    {
        try {
            $user = $this->userService->deleteAvatar($id);

            return response()->json([
                'message' => 'Avatar deleted successfully',
                'data' => $user->toArray(),
            ]);
        } catch (ResourceNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
