<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\DTOs\UserDTO;
use App\Exceptions\ValidationException;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected UserService $userService
    ) {
    }

    /**
     * Register a new user.
     */
    public function register(UserDTO $userDTO, ?string $roleName = 'user'): array
    {
        // Check rate limit for registration
        $key = 'register.' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw new ValidationException('Too many registration attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).');
        }

        // Check if email already exists
        if ($this->userRepository->existsByEmail($userDTO->email)) {
            RateLimiter::hit($key, 600); // 10 minutes lockout
            throw new ValidationException('Email already exists');
        }

        // Create user
        $createdUser = $this->userService->create($userDTO, $roleName);

        // Generate token
        $user = $this->userRepository->findOrFail($createdUser->id);
        $token = $user->createToken('auth_token')->plainTextToken;

        // Clear rate limiter on success
        RateLimiter::clear($key);

        return [
            'user' => $createdUser,
            'token' => $token,
        ];
    }

    /**
     * Login user.
     */
    public function login(LoginDTO $loginDTO): array
    {
        // Use rate limiter for login attempts
        $key = 'login.' . $loginDTO->email . '|' . request()->ip();

        // Check rate limit for login attempts (5 attempts per 5 minutes)
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw LaravelValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).'],
            ]);
        }

        // Attempt authentication
        if (! Auth::attempt($loginDTO->toArray())) {
            RateLimiter::hit($key, 300); // 5 minutes lockout

            throw LaravelValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // Get authenticated user
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Load roles and permissions
        $userDTO = $this->userService->getByIdWithRelations($user->id);

        return [
            'user' => $userDTO,
            'token' => $token,
            'permissions' => $userDTO->permissions ?? [],
            'roles' => $userDTO->roles ?? [],
        ];
    }

    /**
     * Logout user.
     */
    public function logout(): bool
    {
        $user = Auth::user();
        if ($user) {
            $user->currentAccessToken()->delete();
            return true;
        }

        return false;
    }

    /**
     * Get authenticated user.
     */
    public function me(): UserDTO
    {
        $user = Auth::user();
        if (! $user) {
            throw new ValidationException('User not authenticated');
        }

        return $this->userService->getByIdWithRelations($user->id);
    }

    /**
     * Refresh token.
     */
    public function refresh(): string
    {
        $user = Auth::user();
        if (! $user) {
            throw new ValidationException('User not authenticated');
        }

        // Delete current token
        $user->currentAccessToken()->delete();

        // Create new token
        return $user->createToken('auth_token')->plainTextToken;
    }
}
