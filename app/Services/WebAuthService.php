<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Events\Registered;

class WebAuthService
{
    public function __construct(
        protected UserService $userService,
        protected UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Register a new user for web flow and fire verification event.
     */
    public function register(UserDTO $userDTO, string $roleName = 'user'): User
    {
        $created = $this->userService->create($userDTO, $roleName);
        $user = $this->userRepository->findOrFail($created->id);

        event(new Registered($user));

        return $user;
    }
}
