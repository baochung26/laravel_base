<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Storage\StorageService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService extends BaseService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected StorageService $storageService
    ) {
        parent::__construct($userRepository);
    }

    /**
     * Get all users.
     */
    public function getAll(array $columns = ['*']): Collection
    {
        return $this->userRepository->getAllWithRoles($columns);
    }

    /**
     * Get all users with roles and permissions.
     */
    public function getAllWithRelations(array $columns = ['*']): Collection
    {
        return $this->userRepository->getAllWithRelations($columns);
    }

    /**
     * Get user by ID.
     */
    public function getById(int $id): UserDTO
    {
        $user = $this->findOrFail($id);

        return UserDTO::fromModel($user);
    }

    /**
     * Get user by ID with roles and permissions.
     */
    public function getByIdWithRelations(int $id): UserDTO
    {
        $user = $this->userRepository->withRolesAndPermissions($id);

        if (! $user) {
            throw new ResourceNotFoundException("User with ID {$id} not found");
        }

        return UserDTO::fromModel($user);
    }

    /**
     * Get user by email.
     */
    public function getByEmail(string $email): ?UserDTO
    {
        $user = $this->userRepository->findByEmail($email);

        return $user ? UserDTO::fromModel($user) : null;
    }

    /**
     * Create a new user.
     */
    public function create(UserDTO $userDTO, ?string $roleName = null): UserDTO
    {
        // Check if email already exists
        if ($this->userRepository->existsByEmail($userDTO->email)) {
            throw new ValidationException('Email already exists');
        }

        // Hash password if provided
        $data = $userDTO->toCreateArray();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Create user
        $user = $this->userRepository->create($data);

        // Assign role if provided
        if ($roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->assignRole($role);
            }
        }

        return UserDTO::fromModel($user->load('roles', 'permissions'));
    }

    /**
     * Create user with optional avatar file. Returns user model with relations for API resource.
     */
    public function createWithAvatar(UserDTO $userDTO, ?UploadedFile $avatarFile = null, ?string $roleName = null)
    {
        $created = $this->create($userDTO, $roleName);
        if ($created->id && $avatarFile) {
            $this->setAvatarFromFile($created->id, $avatarFile);
        }

        return $this->getModelByIdWithRelations($created->id);
    }

    /**
     * Update user with optional avatar file. Returns user model with relations.
     */
    public function updateWithAvatar(int $id, UserDTO $userDTO, ?UploadedFile $avatarFile = null)
    {
        $dto = $userDTO;
        if ($avatarFile) {
            $user = $this->userRepository->findOrFail($id);
            if ($user->avatar) {
                $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
            }
            $path = $this->storageService->storeAvatar($avatarFile, $id, config('constants.uploads.avatar_disk'));
            $dto = UserDTO::fromArray(array_merge($userDTO->toArray(), ['avatar' => $path]));
        }
        $this->update($id, $dto);

        return $this->getModelByIdWithRelations($id);
    }

    /**
     * Set user avatar from uploaded file (replaces existing avatar).
     */
    public function setAvatarFromFile(int $userId, UploadedFile $file): void
    {
        $user = $this->userRepository->findOrFail($userId);
        if ($user->avatar) {
            $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
        }
        $path = $this->storageService->storeAvatar($file, $userId, config('constants.uploads.avatar_disk'));
        $this->userRepository->update($userId, ['avatar' => $path]);
    }

    /**
     * Update user.
     */
    public function update(int $id, UserDTO $userDTO): UserDTO
    {
        // Check if user exists
        $user = $this->userRepository->findOrFail($id);

        // Check if email is being changed and if it's already taken
        if ($userDTO->email !== $user->email && $this->userRepository->existsByEmail($userDTO->email)) {
            throw new ValidationException('Email already exists');
        }

        // Prepare update data
        $data = [
            'name' => $userDTO->name,
            'email' => $userDTO->email,
        ];

        // Update password if provided
        if ($userDTO->password) {
            $data['password'] = Hash::make($userDTO->password);
        }

        // Update avatar if provided
        if ($userDTO->avatar) {
            $data['avatar'] = $userDTO->avatar;
        }

        // Update user
        $this->userRepository->update($id, $data);

        // Reload user with relations
        $updatedUser = $this->userRepository->withRolesAndPermissions($id);

        return UserDTO::fromModel($updatedUser);
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(int $id, string $avatarPath): UserDTO
    {
        // Check if user exists
        $this->userRepository->findOrFail($id);

        // Update avatar
        $this->userRepository->update($id, ['avatar' => $avatarPath]);

        // Reload user with relations
        $updatedUser = $this->userRepository->withRolesAndPermissions($id);

        return UserDTO::fromModel($updatedUser);
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(int $id): UserDTO
    {
        $user = $this->userRepository->findOrFail($id);
        if ($user->avatar) {
            $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
        }
        $this->userRepository->update($id, ['avatar' => null]);
        $updatedUser = $this->userRepository->withRolesAndPermissions($id);

        return UserDTO::fromModel($updatedUser);
    }

    /**
     * Update user profile (name, email, avatar).
     */
    public function updateProfile(int $id, UserDTO $userDTO): UserDTO
    {
        // Check if user exists
        $user = $this->userRepository->findOrFail($id);

        // Check if email is being changed and if it's already taken
        if ($userDTO->email !== $user->email && $this->userRepository->existsByEmail($userDTO->email)) {
            throw new ValidationException('Email already exists');
        }

        // Prepare update data
        $data = [
            'name' => $userDTO->name,
            'email' => $userDTO->email,
        ];

        if ($userDTO->avatar) {
            if ($user->avatar) {
                $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
            }
            $data['avatar'] = $userDTO->avatar;
        }

        $this->userRepository->update($id, $data);
        $updatedUser = $this->userRepository->withRolesAndPermissions($id);

        return UserDTO::fromModel($updatedUser);
    }

    /**
     * Update profile with optional avatar file. Returns user model with relations.
     */
    public function updateProfileWithAvatar(int $id, UserDTO $userDTO, ?UploadedFile $avatarFile = null)
    {
        $dto = $userDTO;
        if ($avatarFile) {
            $user = $this->userRepository->findOrFail($id);
            if ($user->avatar) {
                $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
            }
            $path = $this->storageService->storeAvatar($avatarFile, $id, config('constants.uploads.avatar_disk'));
            $dto = UserDTO::fromArray(array_merge($userDTO->toArray(), ['avatar' => $path]));
        }
        $this->updateProfile($id, $dto);

        return $this->getModelByIdWithRelations($id);
    }

    /**
     * Change user password.
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        // Check if user exists
        $user = $this->userRepository->findOrFail($id);

        // Verify current password
        if (! Hash::check($currentPassword, $user->password)) {
            throw new ValidationException('Current password is incorrect');
        }

        // Update password
        $this->userRepository->update($id, [
            'password' => Hash::make($newPassword),
        ]);

        return true;
    }

    /**
     * Delete user and remove avatar file if present.
     */
    public function delete(int $id): bool
    {
        $user = $this->userRepository->findOrFail($id);
        if ($user->avatar) {
            $this->storageService->delete($user->avatar, config('constants.uploads.avatar_disk'));
        }

        return $this->deleteRecord($id);
    }

    /**
     * Search users.
     */
    public function search(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->search($keyword, $perPage);
    }

    /**
     * Assign role to user.
     */
    public function assignRole(int $userId, string $roleName): UserDTO
    {
        $user = $this->userRepository->withRolesAndPermissions($userId);
        if (! $user) {
            throw new ResourceNotFoundException("User with ID {$userId} not found");
        }

        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            throw new ValidationException("Role '{$roleName}' not found");
        }

        $user->assignRole($role);

        // Reload with fresh relations
        return UserDTO::fromModel($user->load('roles', 'permissions'));
    }

    /**
     * Remove role from user.
     */
    public function removeRole(int $userId, string $roleName): UserDTO
    {
        $user = $this->userRepository->withRolesAndPermissions($userId);
        if (! $user) {
            throw new ResourceNotFoundException("User with ID {$userId} not found");
        }

        $user->removeRole($roleName);

        // Reload with fresh relations
        return UserDTO::fromModel($user->load('roles', 'permissions'));
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(int $userId, array $roleNames): UserDTO
    {
        $user = $this->userRepository->withRolesAndPermissions($userId);
        if (! $user) {
            throw new ResourceNotFoundException("User with ID {$userId} not found");
        }

        // Validate all roles exist
        $roles = Role::whereIn('name', $roleNames)->get();
        if ($roles->count() !== count($roleNames)) {
            throw new ValidationException('One or more roles not found');
        }

        $user->syncRoles($roleNames);

        // Reload with fresh relations
        return UserDTO::fromModel($user->load('roles', 'permissions'));
    }

    /**
     * Get paginated users with relations.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->userRepository->paginateWithRelations($perPage, $columns);
    }

    /**
     * Get user model by ID with relations (for Resource usage).
     */
    public function getModelByIdWithRelations(int $id)
    {
        $user = $this->userRepository->withRolesAndPermissions($id);
        if (! $user) {
            throw new ResourceNotFoundException("User with ID {$id} not found");
        }

        return $user;
    }

    /**
     * Give permission to user.
     */
    public function givePermissionTo(int $userId, string $permissionName): UserDTO
    {
        $user = $this->userRepository->withRolesAndPermissions($userId);
        if (! $user) {
            throw new ResourceNotFoundException("User with ID {$userId} not found");
        }

        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
        if (! $permission) {
            throw new ValidationException("Permission '{$permissionName}' not found");
        }

        $user->givePermissionTo($permission);

        // Reload with fresh relations
        return UserDTO::fromModel($user->load('roles', 'permissions'));
    }

    /**
     * Revoke permission from user.
     */
    public function revokePermissionFrom(int $userId, string $permissionName): UserDTO
    {
        $user = $this->userRepository->withRolesAndPermissions($userId);
        if (! $user) {
            throw new ResourceNotFoundException("User with ID {$userId} not found");
        }

        $user->revokePermissionTo($permissionName);

        // Reload with fresh relations
        return UserDTO::fromModel($user->load('roles', 'permissions'));
    }
}
