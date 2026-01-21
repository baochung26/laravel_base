<?php

namespace App\DTOs;

class UserDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password = null,
        public readonly ?string $avatar = null,
        public readonly ?array $roles = null,
        public readonly ?array $permissions = null,
    ) {
    }

    /**
     * Create DTO from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
            avatar: $data['avatar'] ?? null,
            roles: $data['roles'] ?? null,
            permissions: $data['permissions'] ?? null,
        );
    }

    /**
     * Create DTO from User model.
     */
    public static function fromModel($user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            password: null,
            avatar: $user->avatar,
            roles: $user->relationLoaded('roles') ? $user->roles->pluck('name')->toArray() : null,
            permissions: $user->relationLoaded('permissions') ? $user->permissions->pluck('name')->toArray() : null,
        );
    }

    /**
     * Convert DTO to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'roles' => $this->roles,
            'permissions' => $this->permissions,
        ], fn ($value) => $value !== null);
    }

    /**
     * Get only data for creating user (without id, roles, permissions).
     */
    public function toCreateArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'avatar' => $this->avatar,
        ], fn ($value) => $value !== null);
    }
}
